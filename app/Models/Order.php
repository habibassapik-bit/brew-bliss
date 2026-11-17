<?php

namespace App\Models;

use App\Enums\FulfillmentType;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

#[Fillable([
    'order_number', 'user_id', 'voucher_id', 'status', 'payment_status', 'fulfillment_type',
    'customer_name', 'customer_phone', 'delivery_address', 'subtotal', 'discount_amount',
    'delivery_fee', 'total', 'note', 'scheduled_at', 'cancellation_reason', 'paid_at', 'completed_at',
])]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'fulfillment_type' => FulfillmentType::class,
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'total' => 'decimal:2',
            'scheduled_at' => 'datetime',
            'paid_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Voucher, $this>
     */
    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return HasMany<OrderStatusHistory, $this>
     */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    /**
     * @return HasOne<Payment, $this>
     */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Daftar status yang dapat dituju dari status saat ini (sumber tunggal aturan transisi).
     *
     * @return list<OrderStatus>
     */
    public function allowedTransitions(): array
    {
        return match ($this->status) {
            OrderStatus::PendingConfirmation => [OrderStatus::AwaitingPayment, OrderStatus::Processing, OrderStatus::Cancelled],
            OrderStatus::AwaitingPayment => [OrderStatus::Processing, OrderStatus::Cancelled],
            OrderStatus::Processing => [OrderStatus::ReadyPickup, OrderStatus::Shipping, OrderStatus::Completed, OrderStatus::Cancelled],
            OrderStatus::ReadyPickup => [OrderStatus::Completed, OrderStatus::Cancelled],
            OrderStatus::Shipping => [OrderStatus::Completed, OrderStatus::Cancelled],
            OrderStatus::Completed, OrderStatus::Cancelled => [],
        };
    }

    /**
     * Tentukan apakah perpindahan status saat ini ke status tujuan diizinkan.
     */
    public function canTransitionTo(OrderStatus $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }

    /**
     * Tentukan apakah customer masih dapat membatalkan pesanan ini.
     */
    public function canBeCancelledByCustomer(): bool
    {
        return $this->canTransitionTo(OrderStatus::Cancelled)
            && $this->payment_status === PaymentStatus::Unpaid;
    }

    /**
     * Terapkan perubahan status dengan pemeriksaan transisi dan pencatatan riwayat.
     * Mengembalikan null jika transisi tidak valid.
     */
    public function applyStatusTransition(OrderStatus $target, ?string $note = null, ?int $actorId = null): ?OrderStatusHistory
    {
        if (! $this->canTransitionTo($target)) {
            return null;
        }

        $previous = $this->status;

        return DB::transaction(function () use ($target, $previous, $note, $actorId): ?OrderStatusHistory {
            $updated = static::query()
                ->whereKey($this->id)
                ->where('status', $previous->value)
                ->update(['status' => $target->value]);

            if ($updated === 0) {
                return null;
            }

            $this->status = $target;

            $history = $this->statusHistories()->create([
                'changed_by' => $actorId,
                'status' => $target,
                'note' => $note ?? "Status berubah dari {$previous->label()} menjadi {$target->label()}.",
            ]);

            if ($target === OrderStatus::Cancelled) {
                $this->restoreStock();
            }

            return $history;
        });
    }

    /**
     * Kembalikan stok produk pada pesanan yang dibatalkan (dipanggil sekali saat transisi ke dibatalkan).
     */
    private function restoreStock(): void
    {
        foreach ($this->items()->get() as $item) {
            Product::withTrashed()
                ->whereKey($item->product_id)
                ->increment('stock', $item->quantity);
        }
    }
}
