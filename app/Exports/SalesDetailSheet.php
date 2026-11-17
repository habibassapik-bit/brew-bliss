<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class SalesDetailSheet implements FromArray, ShouldAutoSize, WithHeadings, WithTitle
{
    /**
     * @param  list<array<string, mixed>>  $detail
     */
    public function __construct(
        private readonly array $detail,
    ) {}

    /**
     * @return list<list<mixed>>
     */
    public function array(): array
    {
        return array_map(
            fn (array $row): array => [
                $row['order_number'],
                $row['created_at'],
                $row['customer_name'],
                $row['items_count'],
                $row['subtotal'],
                $row['discount'],
                $row['delivery_fee'],
                $row['total'],
                $row['status'],
                $row['payment_status'],
            ],
            $this->detail,
        );
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return [
            'Nomor Pesanan',
            'Tanggal',
            'Customer',
            'Jumlah Item',
            'Subtotal',
            'Diskon',
            'Ongkir',
            'Total',
            'Status Pesanan',
            'Status Pembayaran',
        ];
    }

    public function title(): string
    {
        return 'Detail Transaksi';
    }
}
