<?php

namespace App\Http\Controllers\Admin;

use App\Enums\VoucherType;
use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class VoucherController extends Controller
{
    /**
     * Tampilkan daftar voucher.
     */
    public function index(): View
    {
        $vouchers = Voucher::query()->latest()->paginate(15);

        return view('admin.vouchers.index', [
            'vouchers' => $vouchers,
        ]);
    }

    /**
     * Tampilkan formulir voucher baru.
     */
    public function create(): View
    {
        return view('admin.vouchers.form', [
            'voucher' => new Voucher,
        ]);
    }

    /**
     * Simpan voucher baru dengan validasi kode unik dan periode berlaku.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateVoucher($request);

        $voucher = Voucher::query()->create($data);

        ActivityLogger::log('voucher.created', $voucher, [
            'description' => "Menambah voucher {$voucher->code}.",
        ]);

        return redirect()
            ->route('admin.vouchers.index')
            ->with('status', 'Voucher berhasil dibuat.');
    }

    /**
     * Tampilkan formulir edit voucher.
     */
    public function edit(Voucher $voucher): View
    {
        return view('admin.vouchers.form', [
            'voucher' => $voucher,
        ]);
    }

    /**
     * Perbarui voucher tanpa menyentuh riwayat penggunaan (used_count).
     */
    public function update(Request $request, Voucher $voucher): RedirectResponse
    {
        $data = $this->validateVoucher($request, $voucher);

        $voucher->update($data);

        ActivityLogger::log('voucher.updated', $voucher, [
            'description' => "Memperbarui voucher {$voucher->code}.",
        ]);

        return redirect()
            ->route('admin.vouchers.index')
            ->with('status', 'Voucher berhasil diperbarui.');
    }

    /**
     * Nonaktifkan voucher (riwayat transaksi tetap utuh).
     */
    public function destroy(Voucher $voucher): RedirectResponse
    {
        $voucher->update(['is_active' => false]);

        ActivityLogger::log('voucher.deactivated', $voucher, [
            'description' => "Menonaktifkan voucher {$voucher->code}.",
        ]);

        return redirect()
            ->route('admin.vouchers.index')
            ->with('status', 'Voucher dinonaktifkan. Pesanan lama tidak terpengaruh.');
    }

    /**
     * Validasi input voucher.
     *
     * @return array<string, mixed>
     */
    private function validateVoucher(Request $request, ?Voucher $voucher = null): array
    {
        $data = $request->validate([
            'code' => [
                'required', 'string', 'max:50',
                Rule::unique('vouchers')->ignore($voucher?->id),
            ],
            'type' => ['required', 'string', 'in:'.implode(',', array_map(fn (VoucherType $type) => $type->value, VoucherType::cases()))],
            'value' => ['required', 'numeric', 'min:0'],
            'min_purchase' => ['nullable', 'numeric', 'min:0'],
            'max_discount' => ['nullable', 'numeric', 'min:0'],
            'quota' => ['nullable', 'integer', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'code.unique' => 'Kode voucher sudah digunakan.',
            'ends_at.after_or_equal' => 'Tanggal berakhir tidak boleh sebelum tanggal mulai.',
        ]);

        if ($data['type'] === VoucherType::Percent->value && (float) $data['value'] > 100) {
            $this->rejectField('value', 'Nilai diskon persen maksimal 100.');
        }

        if ($data['type'] === VoucherType::Fixed->value && (float) $data['value'] <= 0) {
            $this->rejectField('value', 'Nilai diskon nominal harus lebih dari 0.');
        }

        return [
            'code' => strtoupper(trim($data['code'])),
            'type' => $data['type'],
            'value' => $data['value'],
            'min_purchase' => $data['min_purchase'] ?? 0,
            'max_discount' => $data['max_discount'] ?? null,
            'quota' => $data['quota'] ?? null,
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ];
    }

    /**
     * Lemparkan kesalahan validasi untuk satu kolom tertentu.
     */
    private function rejectField(string $field, string $message): never
    {
        throw ValidationException::withMessages([
            $field => $message,
        ]);
    }
}
