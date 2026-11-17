<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class SalesSummarySheet implements FromArray, ShouldAutoSize, WithHeadings, WithTitle
{
    /**
     * @param  list<array{0: string, 1: mixed}>  $summary
     */
    public function __construct(
        private readonly array $summary,
    ) {}

    /**
     * @return list<list<mixed>>
     */
    public function array(): array
    {
        return array_map(
            fn (array $row): array => [$row[0], $row[1]],
            $this->summary,
        );
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return ['Keterangan', 'Nilai'];
    }

    public function title(): string
    {
        return 'Ringkasan';
    }
}
