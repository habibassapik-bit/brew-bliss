<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentController extends Controller
{
    private const PROOF_DISK = 'local';

    /**
     * Simpan bukti pembayaran milik order customer (unggah awal atau penggantian).
     */
    public function store(Request $request, Order $order): RedirectResponse
    {
        abort_if($order->user_id !== auth()->id(), 404);

        $data = $request->validate([
            'proof' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'payer_name' => ['nullable', 'string', 'max:255'],
            'reference_number' => ['nullable', 'string', 'max:100'],
        ]);

        $payment = $order->payment;

        if ($payment === null) {
            throw ValidationException::withMessages([
                'proof' => 'Pesanan ini belum memiliki data pembayaran.',
            ]);
        }

        if (! $payment->method->requiresProof()) {
            throw ValidationException::withMessages([
                'proof' => 'Metode pembayaran ini tidak memerlukan bukti pembayaran.',
            ]);
        }

        if (! in_array($payment->status, [PaymentStatus::Unpaid, PaymentStatus::PendingVerification, PaymentStatus::Rejected], true)) {
            throw ValidationException::withMessages([
                'proof' => 'Bukti pembayaran tidak dapat diunggah pada status pembayaran saat ini.',
            ]);
        }

        if (in_array($order->status, [OrderStatus::Cancelled, OrderStatus::Completed], true)) {
            throw ValidationException::withMessages([
                'proof' => 'Pesanan sudah selesai atau dibatalkan dan tidak dapat menerima bukti pembayaran baru.',
            ]);
        }

        $path = Storage::disk(self::PROOF_DISK)->putFile('payment-proofs', $data['proof']);

        $oldProofPath = $payment->proof_path;

        $payment->update([
            'proof_path' => $path,
            'payer_name' => $data['payer_name'] ?? null,
            'reference_number' => $data['reference_number'] ?? null,
            'status' => PaymentStatus::PendingVerification,
            'rejection_reason' => null,
            'verified_by' => null,
            'verified_at' => null,
        ]);

        if ($oldProofPath !== null && $oldProofPath !== $path) {
            Storage::disk(self::PROOF_DISK)->delete($oldProofPath);
        }

        $order->update(['payment_status' => PaymentStatus::PendingVerification]);

        return redirect()
            ->route('orders.show', $order)
            ->with('status', 'Bukti pembayaran terkirim dan menunggu verifikasi penjual.');
    }

    /**
     * Tampilkan file bukti pembayaran (hanya pemilik order atau Super Admin).
     */
    public function proof(Order $order): StreamedResponse
    {
        abort_if($order->user_id !== auth()->id() && ! auth()->user()->hasRole('super_admin'), 404);

        $payment = $order->payment;

        if ($payment === null || $payment->proof_path === null || ! Storage::disk(self::PROOF_DISK)->exists($payment->proof_path)) {
            abort(404);
        }

        return Storage::disk(self::PROOF_DISK)->response($payment->proof_path);
    }
}
