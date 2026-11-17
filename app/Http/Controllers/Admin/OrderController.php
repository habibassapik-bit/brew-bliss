<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\ActivityLogger;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Tampilkan daftar pesanan dengan pencarian dan filter status.
     */
    public function index(Request $request): View
    {
        $search = (string) $request->string('q');
        $status = (string) $request->string('status');

        $orders = Order::query()
            ->with('payment')
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $inner) => $inner
                ->where('order_number', 'like', "%{$search}%")
                ->orWhere('customer_name', 'like', "%{$search}%")))
            ->when(in_array($status, OrderStatus::values(), true), fn (Builder $query) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'statuses' => OrderStatus::cases(),
            'search' => $search,
            'statusFilter' => $status,
        ]);
    }

    /**
     * Tampilkan detail pesanan untuk Super Admin.
     */
    public function show(Order $order): View
    {
        $order->load(['items', 'payment', 'statusHistories.changedBy', 'user']);

        return view('admin.orders.show', [
            'order' => $order,
        ]);
    }

    /**
     * Ubah status pesanan sesuai aturan transisi yang valid.
     */
    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', OrderStatus::values())],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $target = OrderStatus::from($data['status']);

        if ($target === OrderStatus::Cancelled) {
            return $this->cancel($request, $order);
        }

        $history = $order->applyStatusTransition($target, $data['note'] ?? null, auth()->id());

        if ($history === null) {
            throw ValidationException::withMessages([
                'status' => "Perubahan status dari {$order->status->label()} ke {$target->label()} tidak diizinkan.",
            ]);
        }

        ActivityLogger::log('order.status_changed', $order, [
            'description' => "Mengubah status pesanan {$order->order_number} menjadi {$target->label()}.",
            'from' => $order->status->value,
            'to' => $target->value,
        ]);

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('status', "Status pesanan {$order->order_number} diperbarui menjadi {$target->label()}.");
    }

    /**
     * Batalkan pesanan dengan alasan dan kembalikan stok.
     */
    public function cancel(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'cancellation_reason' => ['required', 'string', 'min:5', 'max:255'],
        ]);

        $history = $order->applyStatusTransition(
            OrderStatus::Cancelled,
            'Dibatalkan oleh admin: '.$data['cancellation_reason'],
            auth()->id(),
        );

        if ($history === null) {
            throw ValidationException::withMessages([
                'status' => 'Pesanan tidak dapat dibatalkan pada status saat ini.',
            ]);
        }

        $order->update(['cancellation_reason' => $data['cancellation_reason']]);

        ActivityLogger::log('order.cancelled', $order, [
            'description' => "Membatalkan pesanan {$order->order_number}: {$data['cancellation_reason']}.",
        ]);

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('status', "Pesanan {$order->order_number} dibatalkan dan stok dikembalikan.");
    }
}
