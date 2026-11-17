<?php

namespace App\Models;

use App\Enums\VoucherType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['code', 'type', 'value', 'min_purchase', 'max_discount', 'quota', 'used_count', 'starts_at', 'ends_at', 'is_active'])]
class Voucher extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => VoucherType::class,
            'value' => 'decimal:2',
            'min_purchase' => 'decimal:2',
            'max_discount' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Tentukan apakah voucher dapat digunakan (aktif, dalam periode, dan kuota tersisa).
     */
    public function isUsable(): bool
    {
        return $this->is_active
            && $this->isWithinPeriod()
            && $this->hasQuotaLeft();
    }

    /**
     * Tentukan apakah voucher berada dalam periode berlakunya.
     */
    public function isWithinPeriod(): bool
    {
        $now = now();

        return ($this->starts_at === null || $this->starts_at->lte($now))
            && ($this->ends_at === null || $this->ends_at->gte($now));
    }

    /**
     * Tentukan apakah kuota penggunaan voucher masih tersisa.
     */
    public function hasQuotaLeft(): bool
    {
        return $this->quota === null || $this->used_count < $this->quota;
    }

    /**
     * Tentukan apakah subtotal memenuhi minimum pembelian voucher.
     */
    public function meetsMinimum(float $subtotal): bool
    {
        return $subtotal >= (float) $this->min_purchase;
    }

    /**
     * Hitung nilai diskon voucher untuk subtotal tertentu (0 jika tidak memenuhi syarat).
     */
    public function discountFor(float $subtotal): float
    {
        if (! $this->isUsable() || ! $this->meetsMinimum($subtotal)) {
            return 0.0;
        }

        $discount = match ($this->type) {
            VoucherType::Fixed => min((float) $this->value, $subtotal),
            VoucherType::Percent => round($subtotal * (float) $this->value / 100, 2),
        };

        if ($this->max_discount !== null) {
            $discount = min($discount, (float) $this->max_discount);
        }

        return (float) min($discount, $subtotal);
    }

    /**
     * Pesan mengapa voucher tidak dapat digunakan, null jika dapat digunakan.
     */
    public function unusableReason(float $subtotal): ?string
    {
        if (! $this->is_active) {
            return 'Voucher tidak aktif.';
        }

        if ($this->starts_at !== null && $this->starts_at->isAfter(now())) {
            return 'Voucher belum berlaku.';
        }

        if ($this->ends_at !== null && $this->ends_at->isBefore(now())) {
            return 'Voucher sudah kedaluwarsa.';
        }

        if (! $this->hasQuotaLeft()) {
            return 'Kuota penggunaan voucher sudah habis.';
        }

        if (! $this->meetsMinimum($subtotal)) {
            return sprintf('Minimum pembelian voucher ini Rp %s.', number_format((float) $this->min_purchase, 0, ',', '.'));
        }

        return null;
    }
}
