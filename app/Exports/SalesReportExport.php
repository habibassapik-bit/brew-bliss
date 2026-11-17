<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SalesReportExport implements Export, WithMultipleSheets
{
    /**
     * @param  list<array{0: string, 1: mixed}>  $summary
     * @param  list<array<string, mixed>>  $detail
     */
    public function __construct(
        public readonly array $summary,
        public readonly array $detail,
    ) {}

    /**
     * @return list<SalesSummarySheet|SalesDetailSheet>
     */
    public function sheets(): array
    {
        return [
            new SalesSummarySheet($this->summary),
            new SalesDetailSheet($this->detail),
        ];
    }
}
