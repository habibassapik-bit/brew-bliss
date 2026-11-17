<?php

namespace App\Http\Controllers;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\Setting;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Tampilkan halaman FAQ (pertanyaan yang sering diajukan).
     */
    public function faq(): View
    {
        $faqs = [
            [
                'question' => 'Bagaimana cara membuat pesanan?',
                'answer' => 'Pilih produk di halaman Katalog, atur jumlahnya, lalu tambahkan ke keranjang. Dari halaman Keranjang, lanjutkan ke Checkout, isi data pelanggan, pilih metode Pemesanan (Pengiriman atau Ambil Sendiri), lalu klik Buat Pesanan.',
            ],
            [
                'question' => 'Bagaimana cara menggunakan voucher?',
                'answer' => 'Di halaman Checkout, masukkan kode voucher pada kolom Voucher lalu klik Terapkan. Diskon dihitung otomatis pada ringkasan pesanan jika kode valid dan syarat minimum belanja terpenuhi.',
            ],
            [
                'question' => 'Bagaimana cara melakukan pembayaran?',
                'answer' => 'Setelah pesanan dibuat, lakukan pembayaran sesuai metode yang tersedia (Transfer Bank, QRIS, atau E-Wallet). Untuk metode yang memerlukan bukti, unggah foto bukti pembayaran pada halaman detail pesanan. Pesanan akan diverifikasi oleh penjual.',
            ],
            [
                'question' => 'Bagaimana cara melihat status pesanan saya?',
                'answer' => 'Buka halaman Pesanan untuk melihat daftar pesanan beserta statusnya: Menunggu Konfirmasi, Menunggu Pembayaran, Diproses, Siap Diambil, Dalam Pengiriman, Selesai, atau Dibatalkan.',
            ],
            [
                'question' => 'Berapa biaya pengiriman (ongkir)?',
                'answer' => 'Biaya ongkir untuk layanan Pengiriman adalah Rp3.000 per pesanan. Layanan Ambil Sendiri tidak dikenakan ongkir — pesanan dapat diambil langsung di alamat toko yang tercantum pada halaman Checkout.',
            ],
            [
                'question' => 'Apakah pesanan saya bisa dibatalkan?',
                'answer' => 'Ya. Selama pesanan masih berada pada tahap awal, buka detail pesanan di halaman Pesanan lalu gunakan tombol Batalkan dengan mengisi alasan pembatalan. Setelah pesanan diproses penjual, pesanan tidak dapat dibatalkan lagi.',
            ],
            [
                'question' => 'Bagaimana cara menghubungi customer service?',
                'answer' => 'Buka halaman Bantuan. Kamu dapat mengirim pesan langsung kepada tim Brew & Bliss dan melihat balasannya di halaman yang sama.',
            ],
        ];

        return view('faq.index', [
            'faqs' => collect($faqs),
        ]);
    }

    /**
     * Tampilkan beranda customer.
     */
    public function index(): View
    {
        $featuredProducts = Product::query()
            ->where('status', ProductStatus::Available->value)
            ->where('stock', '>', 0)
            ->with('category')
            ->withCount('orderItems')
            ->orderByDesc('order_items_count')
            ->orderByDesc('created_at')
            ->limit(4)
            ->get();

        $latestProducts = Product::query()
            ->where('status', ProductStatus::Available->value)
            ->where('stock', '>', 0)
            ->with('category')
            ->latest()
            ->limit(4)
            ->get();

        $categories = Category::query()
            ->where('is_active', true)
            ->withCount(['products' => fn ($query) => $query
                ->where('status', ProductStatus::Available->value)
                ->where('stock', '>', 0)])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $reviews = Review::query()
            ->with(['user', 'product'])
            ->latest()
            ->limit(3)
            ->get();

        $storeSettings = collect([
            'store_name' => Setting::get('store_name', 'Brew & Bliss'),
            'store_description' => Setting::get('store_description'),
            'store_address' => Setting::get('store_address'),
            'store_phone' => Setting::get('store_phone'),
            'store_open_hours' => Setting::get('store_open_hours'),
        ]);

        return view('home', [
            'featuredProducts' => $featuredProducts,
            'latestProducts' => $latestProducts,
            'categories' => $categories,
            'reviews' => $reviews,
            'storeSettings' => $storeSettings,
            'wishlistProductIds' => auth()->check()
                ? auth()->user()->wishlists()->pluck('product_id')->all()
                : [],
        ]);
    }
}
