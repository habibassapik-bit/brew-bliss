<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OperationalCost;
use App\Support\ActivityLogger;
use App\Support\ReportPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OperationalCostController extends Controller
{
    /**
     * Kategori biaya yang tersedia (dipisah dari HPP produk).
     */
    public const CATEGORIES = [
        'operational' => 'Operasional',
        'other' => 'Biaya Lain',
    ];

    /**
     * Tampilkan daftar biaya operasional dengan filter periode.
     */
    public function index(): View
    {
        [$period, $start, $end, $from, $to] = ReportPeriod::fromRequest(request());

        $costs = OperationalCost::query()
            ->with('recorder')
            ->whereBetween('incurred_at', [$start, $end])
            ->orderByDesc('incurred_at')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.operational-costs.index', [
            'costs' => $costs,
            'period' => $period,
            'from' => $from,
            'to' => $to,
            'periodLabel' => ReportPeriod::label($period, $from, $to),
            'total' => (float) OperationalCost::query()
                ->whereBetween('incurred_at', [$start, $end])
                ->sum('amount'),
            'categories' => self::CATEGORIES,
        ]);
    }

    /**
     * Tampilkan form biaya operasional baru.
     */
    public function create(): View
    {
        return view('admin.operational-costs.form', [
            'cost' => new OperationalCost,
            'categories' => self::CATEGORIES,
        ]);
    }

    /**
     * Simpan biaya operasional baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $cost = OperationalCost::query()->create($data + ['recorded_by' => auth()->id()]);

        ActivityLogger::log('cost.created', $cost, [
            'description' => "Menambah biaya operasional {$cost->name} sebesar Rp ".number_format((float) $cost->amount, 0, ',', '.').'.',
        ]);

        return redirect()
            ->route('admin.costs.index')
            ->with('status', "Biaya {$cost->name} berhasil dicatat.");
    }

    /**
     * Tampilkan form edit biaya operasional.
     */
    public function edit(OperationalCost $cost): View
    {
        return view('admin.operational-costs.form', [
            'cost' => $cost,
            'categories' => self::CATEGORIES,
        ]);
    }

    /**
     * Perbarui biaya operasional.
     */
    public function update(Request $request, OperationalCost $cost): RedirectResponse
    {
        $data = $this->validated($request);

        $cost->update($data);

        ActivityLogger::log('cost.updated', $cost, [
            'description' => "Memperbarui biaya operasional {$cost->name}.",
        ]);

        return redirect()
            ->route('admin.costs.index')
            ->with('status', "Biaya {$cost->name} berhasil diperbarui.");
    }

    /**
     * Hapus biaya operasional (soft delete agar laporan lama tetap teraudit).
     */
    public function destroy(OperationalCost $cost): RedirectResponse
    {
        $name = $cost->name;

        $cost->delete();

        ActivityLogger::log('cost.deleted', null, [
            'description' => "Menghapus biaya operasional {$name}.",
        ]);

        return redirect()
            ->route('admin.costs.index')
            ->with('status', "Biaya {$name} berhasil dihapus.");
    }

    /**
     * Validasi input biaya operasional.
     *
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $categories = implode(',', array_keys(self::CATEGORIES));

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'in:'.$categories],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999'],
            'incurred_at' => ['required', 'date', 'before_or_equal:today'],
            'note' => ['nullable', 'string', 'max:1000'],
        ], [
            'incurred_at.before_or_equal' => 'Tanggal biaya tidak boleh di masa depan.',
            'amount.min' => 'Nominal biaya harus lebih dari 0.',
        ]);
    }
}
