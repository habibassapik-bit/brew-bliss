<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Tampilkan riwayat pesanan milik customer yang sedang masuk.
     */
    public function index(): View
    {
        $orders = auth()->user()->orders()
            ->with('payment')
            ->withCount('items')
            ->latest()
            ->paginate(10);

        return view('orders.index', [
            'orders' => $orders,
        ]);
    }

    /**
     * Tampilkan detail pesanan milik customer (snapshot transaksi).
     */
    public function show(Order $order): View
    {
        abort_if($order->user_id !== auth()->id(), 404);

        $order->load(['items', 'payment', 'statusHistories.changedBy']);

        return view('orders.show', [
            'order' => $order,
        ]);
    }

    /**
     * Batalkan pesanan milik customer sesuai aturan transisi status.
     */
    public function cancel(Request $request, Order $order): RedirectResponse
    {
        abort_if($order->user_id !== auth()->id(), 404);

        $data = $request->validate([
            'cancellation_reason' => ['required', 'string', 'min:5', 'max:255'],
        ]);

        $history = $order->applyStatusTransition(
            OrderStatus::Cancelled,
            'Dibatalkan oleh customer: '.$data['cancellation_reason'],
            auth()->id(),
        );

        if ($history === null) {
            throw ValidationException::withMessages([
                'cancellation' => 'Pesanan ini tidak dapat dibatalkan pada status saat ini.',
            ]);
        }

        $order->update(['cancellation_reason' => $data['cancellation_reason']]);

        return redirect()
            ->route('orders.show', $order)
            ->with('status', "Pesanan {$order->order_number} dibatalkan. Stok produk telah dikembalikan.");
    }
}
