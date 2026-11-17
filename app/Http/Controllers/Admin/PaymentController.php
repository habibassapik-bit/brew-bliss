<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Support\ActivityLogger;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PaymentController extends Controller
{
    /**
     * Tampilkan daftar pembayaran dengan penekanan pada yang menunggu verifikasi.
     */
    public function index(Request $request): View
    {
        $status = (string) $request->string('status');

        $payments = Payment::query()
            ->with('order.user')
            ->when($status !== '', fn (Builder $query) => $query->where('status', $status))
            ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [PaymentStatus::PendingVerification->value])
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.payments.index', [
            'payments' => $payments,
            'statuses' => [
                PaymentStatus::Unpaid,
                PaymentStatus::PendingVerification,
                PaymentStatus::Paid,
                PaymentStatus::Rejected,
            ],
            'statusFilter' => $status,
        ]);
    }

    /**
     * Setujui pembayaran dan perbarui status pesanan terkait.
     */
    public function approve(Request $request, Payment $payment): RedirectResponse
    {
        $data = $request->validate([
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        if (! in_array($payment->status, [PaymentStatus::Unpaid, PaymentStatus::PendingVerification], true)) {
            throw ValidationException::withMessages([
                'payment' => 'Pembayaran ini sudah diverifikasi sebelumnya dan tidak dapat diverifikasi ulang.',
            ]);
        }

        DB::transaction(function () use ($payment, $data): void {
            $payment->update([
                'status' => PaymentStatus::Paid,
                'paid_at' => now(),
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'rejection_reason' => null,
            ]);

            $order = $payment->order()->lockForUpdate()->first();

            $order->update(['payment_status' => PaymentStatus::Paid]);

            if ($order->canTransitionTo(OrderStatus::Processing)) {
                $order->applyStatusTransition(
                    OrderStatus::Processing,
                    $data['note'] ?? 'Pembayaran diverifikasi. Pesanan diproses.',
                    auth()->id(),
                );
            }
        });

        ActivityLogger::log('payment.approved', $payment, [
            'description' => "Menyetujui pembayaran untuk pesanan {$payment->order->order_number}.",
        ]);

        return redirect()
            ->back()
            ->with('status', "Pembayaran untuk pesanan {$payment->order->order_number} disetujui.");
    }

    /**
     * Tolak pembayaran dengan alasan (data transaksi tetap disimpan).
     */
    public function reject(Request $request, Payment $payment): RedirectResponse
    {
        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:5', 'max:255'],
        ]);

        if (! in_array($payment->status, [PaymentStatus::Unpaid, PaymentStatus::PendingVerification], true)) {
            throw ValidationException::withMessages([
                'payment' => 'Pembayaran ini sudah diverifikasi sebelumnya dan tidak dapat ditolak ulang.',
            ]);
        }

        DB::transaction(function () use ($payment, $data): void {
            $payment->update([
                'status' => PaymentStatus::Rejected,
                'rejection_reason' => $data['rejection_reason'],
                'verified_by' => auth()->id(),
                'verified_at' => now(),
            ]);

            $payment->order()->update(['payment_status' => PaymentStatus::Rejected]);
        });

        ActivityLogger::log('payment.rejected', $payment, [
            'description' => "Menolak pembayaran untuk pesanan {$payment->order->order_number}: {$data['rejection_reason']}.",
        ]);

        return redirect()
            ->back()
            ->with('status', "Pembayaran untuk pesanan {$payment->order->order_number} ditolak. Customer dapat mengunggah bukti baru.");
    }
}
