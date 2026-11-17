<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ProductStatus;
use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Voucher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    private const VOUCHER_SESSION_KEY = 'checkout_voucher_code';

    /**
     * Tampilkan halaman checkout dengan ringkasan yang dihitung di server.
     */
    public function index(): View|RedirectResponse
    {
        $lines = $this->cartLines();

        if ($lines->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Keranjang masih kosong. Tambahkan produk terlebih dahulu.');
        }

        $voucher = $this->sessionVoucher($this->cartSubtotal($lines));

        return view('checkout.index', [
            'lines' => $lines,
            'subtotal' => $this->cartSubtotal($lines),
            'discount' => $voucher?->discountFor($this->cartSubtotal($lines)) ?? 0.0,
            'voucher' => $voucher,
            'user' => auth()->user(),
            'addresses' => auth()->user()->addresses()->orderByDesc('is_default')->orderBy('created_at')->get(),
            'deliveryEnabled' => Setting::getBool('enable_delivery', true),
            'pickupEnabled' => Setting::getBool('enable_pickup', true),
            'deliveryFee' => (float) Setting::get('delivery_fee', '3000'),
            'storeIsOpen' => Setting::getBool('store_is_open', true),
            'storeOpenHours' => Setting::get('store_open_hours', '-'),
            'storeAddress' => Setting::get('store_address', '-'),
            'activePaymentMethods' => self::activePaymentMethods(),
            'bankAccounts' => self::bankAccounts(),
        ]);
    }

    /**
     * Daftar metode pembayaran aktif dari pengaturan toko.
     *
     * @return list<PaymentMethod>
     */
    public static function activePaymentMethods(): array
    {
        $methods = collect(explode(',', (string) Setting::get('active_payment_methods', '')))
            ->map(fn (string $method) => trim($method))
            ->filter()
            ->map(fn (string $method) => PaymentMethod::tryFrom($method))
            ->filter();

        return $methods->isEmpty()
            ? [PaymentMethod::BankTransfer]
            : $methods->all();
    }

    /**
     * Informasi rekening transfer dari pengaturan toko.
     *
     * @return array<string, mixed>
     */
    public static function bankAccounts(): array
    {
        $decoded = json_decode((string) Setting::get('bank_accounts', '{}'), true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Terapkan voucher ke sesi checkout dengan validasi server.
     */
    public function applyVoucher(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'voucher_code' => ['nullable', 'string', 'max:50'],
        ]);

        $code = trim((string) ($data['voucher_code'] ?? ''));

        if ($code === '') {
            Session::forget(self::VOUCHER_SESSION_KEY);

            return redirect()
                ->route('checkout.index')
                ->with('status', 'Voucher dihapus.');
        }

        $lines = $this->cartLines();

        if ($lines->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Keranjang masih kosong.');
        }

        $subtotal = $this->cartSubtotal($lines);

        $voucher = $this->resolveVoucher($code, $subtotal);

        Session::put(self::VOUCHER_SESSION_KEY, $voucher->code);

        return redirect()
            ->route('checkout.index')
            ->with('status', sprintf('Voucher %s diterapkan. Diskon Rp %s.', $voucher->code, number_format($voucher->discountFor($subtotal), 0, ',', '.')));
    }

    /**
     * Buat pesanan dari keranjang dengan validasi ulang harga, stok, dan voucher.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:20'],
            'fulfillment_type' => ['required', 'string', 'in:delivery,pickup'],
            'address_choice' => ['nullable', 'string', 'max:20'],
            'recipient_name' => ['nullable', 'required_if:address_choice,new', 'string', 'max:255'],
            'address_phone' => ['nullable', 'required_if:address_choice,new', 'string', 'max:20'],
            'address' => ['nullable', 'required_if:address_choice,new', 'string', 'max:500'],
            'note' => ['nullable', 'string', 'max:500'],
            'voucher_code' => ['nullable', 'string', 'max:50'],
            'payment_method' => ['nullable', 'string', 'in:'.implode(',', array_map(fn (PaymentMethod $method) => $method->value, self::activePaymentMethods()))],
            // Kolom bawaan dari manipulasi klien diabaikan; harga dihitung ulang di server.
            'subtotal' => ['nullable'],
            'discount' => ['nullable'],
            'delivery_fee' => ['nullable'],
            'total' => ['nullable'],
        ]);

        $lines = $this->cartLines();

        if ($lines->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Keranjang masih kosong. Tambahkan produk terlebih dahulu.');
        }

        $this->assertStoreOpen();

        $fulfillmentType = $data['fulfillment_type'];

        if ($fulfillmentType === 'delivery' && ! Setting::getBool('enable_delivery', true)) {
            throw ValidationException::withMessages([
                'fulfillment_type' => 'Layanan pengiriman sedang tidak tersedia.',
            ]);
        }

        if ($fulfillmentType === 'pickup' && ! Setting::getBool('enable_pickup', true)) {
            throw ValidationException::withMessages([
                'fulfillment_type' => 'Layanan ambil sendiri sedang tidak tersedia.',
            ]);
        }

        $deliveryAddress = null;

        if ($fulfillmentType === 'delivery') {
            $deliveryAddress = $this->resolveDeliveryAddress($data);
        }

        $subtotal = $this->cartSubtotal($lines);

        $voucherCode = trim((string) ($data['voucher_code'] ?? '')) ?: Session::get(self::VOUCHER_SESSION_KEY);
        $voucher = is_string($voucherCode) && $voucherCode !== ''
            ? $this->resolveVoucher($voucherCode, $subtotal)
            : null;

        $deliveryFee = $fulfillmentType === 'delivery' ? (float) Setting::get('delivery_fee', '3000') : 0.0;

        $order = DB::transaction(function () use ($data, $lines, $fulfillmentType, $deliveryAddress, $voucher, $deliveryFee): Order {
            /** @var array<int, array{item: CartItem, product: Product, quantity: int}> $validated */
            $validated = [];

            foreach ($lines as $line) {
                /** @var Product|null $product */
                $product = Product::query()->whereKey($line['item']->product_id)->lockForUpdate()->first();

                if ($product === null) {
                    throw ValidationException::withMessages([
                        'cart' => 'Beberapa produk sudah tidak tersedia. Perbarui keranjangmu.',
                    ]);
                }

                if ($product->status !== ProductStatus::Available || $product->stock < $line['item']->quantity) {
                    throw ValidationException::withMessages([
                        'cart' => "Stok {$product->name} tidak mencukupi. Perbarui keranjangmu.",
                    ]);
                }

                $validated[] = ['item' => $line['item'], 'product' => $product, 'quantity' => $line['item']->quantity];
            }

            $subtotal = 0.0;
            foreach ($validated as $entry) {
                $subtotal += (float) $entry['product']->price * $entry['quantity'];
            }

            $discount = $voucher?->discountFor($subtotal) ?? 0.0;
            $total = max($subtotal - $discount + $deliveryFee, 0.0);

            if ($voucher !== null) {
                $claimed = Voucher::query()
                    ->whereKey($voucher->id)
                    ->where(fn ($query) => $query->whereNull('quota')->orWhereColumn('used_count', '<', 'quota'))
                    ->increment('used_count');

                if ($claimed === 0) {
                    throw ValidationException::withMessages([
                        'voucher_code' => 'Kuota penggunaan voucher sudah habis.',
                    ]);
                }
            }

            $order = Order::query()->create([
                'order_number' => $this->generateOrderNumber(),
                'user_id' => auth()->id(),
                'voucher_id' => $voucher?->id,
                'status' => OrderStatus::PendingConfirmation,
                'fulfillment_type' => $fulfillmentType,
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'delivery_address' => $deliveryAddress,
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'delivery_fee' => $deliveryFee,
                'total' => $total,
                'note' => $data['note'] ?? null,
            ]);

            foreach ($validated as $entry) {
                $order->items()->create([
                    'product_id' => $entry['product']->id,
                    'product_name' => $entry['product']->name,
                    'product_price' => $entry['product']->price,
                    'product_cost' => $entry['product']->cost_price ?? 0,
                    'quantity' => $entry['quantity'],
                    'subtotal' => (float) $entry['product']->price * $entry['quantity'],
                    'note' => $entry['item']->note,
                ]);

                $decremented = Product::query()
                    ->whereKey($entry['product']->id)
                    ->where('stock', '>=', $entry['quantity'])
                    ->decrement('stock', $entry['quantity']);

                if ($decremented === 0) {
                    throw ValidationException::withMessages([
                        'cart' => "Stok {$entry['product']->name} tidak mencukupi. Perbarui keranjangmu.",
                    ]);
                }
            }

            OrderStatusHistory::query()->create([
                'order_id' => $order->id,
                'changed_by' => auth()->id(),
                'status' => OrderStatus::PendingConfirmation,
                'note' => 'Pesanan dibuat oleh pelanggan.',
            ]);

            $paymentMethod = in_array($data['payment_method'] ?? null, array_map(fn (PaymentMethod $method) => $method->value, self::activePaymentMethods()), true)
                ? PaymentMethod::from($data['payment_method'])
                : self::activePaymentMethods()[0];

            $order->payment()->create([
                'method' => $paymentMethod,
                'status' => PaymentStatus::Unpaid,
                'amount' => $total,
            ]);

            $cartIds = collect($validated)->pluck('item.id')->all();
            CartItem::query()->whereIn('id', $cartIds)->delete();

            return $order;
        });

        Session::forget(self::VOUCHER_SESSION_KEY);

        return redirect()
            ->route('checkout.success', $order)
            ->with('status', "Pesanan {$order->order_number} berhasil dibuat.");
    }

    /**
     * Tampilkan ringkasan pesanan yang baru dibuat (khusus pemilik pesanan).
     */
    public function success(Order $order): View
    {
        abort_if($order->user_id !== auth()->id(), 404);

        $order->load('items');

        return view('checkout.success', [
            'order' => $order,
        ]);
    }

    /**
     * Ambil item keranjang milik user dengan status ketersediaan produk terkini.
     *
     * @return Collection<int, array{item: CartItem, product: Product|null, orderable: bool, line_total: float}>
     */
    private function cartLines(): Collection
    {
        $cart = Cart::query()->where('user_id', auth()->id())->first();

        if ($cart === null) {
            return collect();
        }

        $cart->load(['items.product.category', 'items.product.images']);

        return $cart->items->map(function (CartItem $item): array {
            $product = $item->product;
            $orderable = $product !== null && $product->isOrderable();

            return [
                'item' => $item,
                'product' => $product,
                'orderable' => $orderable,
                'line_total' => $orderable ? (float) $product->price * $item->quantity : 0.0,
            ];
        });
    }

    /**
     * Hitung subtotal keranjang hanya dari item yang dapat dipesan.
     *
     * @param  Collection<int, array{orderable: bool, line_total: float}>  $lines
     */
    private function cartSubtotal(Collection $lines): float
    {
        return (float) $lines->where('orderable', true)->sum('line_total');
    }

    /**
     * Cari voucher berdasarkan kode dan pastikan masih dapat digunakan.
     */
    private function resolveVoucher(string $code, float $subtotal): Voucher
    {
        $voucher = Voucher::query()
            ->where('code', strtoupper($code))
            ->first();

        if ($voucher === null) {
            throw ValidationException::withMessages([
                'voucher_code' => 'Kode voucher tidak ditemukan.',
            ]);
        }

        $reason = $voucher->unusableReason($subtotal);

        if ($reason !== null) {
            Session::forget(self::VOUCHER_SESSION_KEY);

            throw ValidationException::withMessages([
                'voucher_code' => $reason,
            ]);
        }

        return $voucher;
    }

    /**
     * Ambil voucher tersimpan pada sesi checkout, atau buang jika sudah tidak valid.
     */
    private function sessionVoucher(float $subtotal): ?Voucher
    {
        $code = Session::get(self::VOUCHER_SESSION_KEY);

        if (! is_string($code) || $code === '') {
            return null;
        }

        $voucher = Voucher::query()->where('code', $code)->first();

        if ($voucher === null || $voucher->unusableReason($subtotal) !== null) {
            Session::forget(self::VOUCHER_SESSION_KEY);

            return null;
        }

        return $voucher;
    }

    /**
     * Validasi dan snapshot alamat pengiriman milik user.
     *
     * @param  array<string, mixed>  $data
     */
    private function resolveDeliveryAddress(array $data): string
    {
        $choice = $data['address_choice'] ?? null;

        if ($choice === 'new') {
            $newAddress = Address::query()->create([
                'user_id' => auth()->id(),
                'recipient_name' => $data['recipient_name'] ?? '',
                'phone' => $data['address_phone'] ?? '',
                'address' => $data['address'] ?? '',
            ]);

            return $this->formatDeliveryAddress($newAddress);
        }

        if ($choice === null || $choice === '' || ! ctype_digit((string) $choice)) {
            throw ValidationException::withMessages([
                'address_choice' => 'Pilih alamat pengiriman.',
            ]);
        }

        $address = Address::query()
            ->where('id', (int) $choice)
            ->where('user_id', auth()->id())
            ->first();

        if ($address === null) {
            throw ValidationException::withMessages([
                'address_choice' => 'Alamat tidak ditemukan.',
            ]);
        }

        return $this->formatDeliveryAddress($address);
    }

    /**
     * Susun teks snapshot alamat pengiriman untuk pesanan.
     */
    private function formatDeliveryAddress(Address $address): string
    {
        return trim("{$address->recipient_name} ({$address->phone})\n{$address->address}");
    }

    /**
     * Pastikan toko sedang buka sebelum checkout.
     */
    private function assertStoreOpen(): void
    {
        if (! Setting::getBool('store_is_open', true)) {
            throw ValidationException::withMessages([
                'store' => 'Toko sedang tutup. Checkout dapat dilakukan saat toko kembali buka.',
            ]);
        }
    }

    /**
     * Buat nomor pesanan unik.
     */
    private function generateOrderNumber(): string
    {
        do {
            $number = 'BB-'.now()->format('ymd').'-'.strtoupper(Str::random(5));
        } while (Order::query()->where('order_number', $number)->exists());

        return $number;
    }
}
