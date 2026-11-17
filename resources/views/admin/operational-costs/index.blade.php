@extends('layouts.admin')

@section('title', 'Biaya Operasional')

@section('content')
    @php
        /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator<int, \App\Models\OperationalCost> $costs */
        /** @var string $period */
        /** @var ?string $from */
        /** @var ?string $to */
        /** @var array<string, string> $categories */

        $periodOptions = [
            'today' => 'Hari ini',
            'last7' => '7 hari terakhir',
            'month' => 'Bulan ini',
            'year' => 'Tahun ini',
            'custom' => 'Rentang khusus',
        ];
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="heading-1">Biaya Operasional</h1>
            <p class="mt-1 text-sm text-brand-400">Biaya terpisah dari HPP produk; dipakai dalam perhitungan laba bersih.</p>
        </div>
        <a href="{{ route('admin.costs.create') }}" class="btn btn-primary">Tambah Biaya</a>
    </div>

    <form method="GET" action="{{ route('admin.costs.index') }}" class="card mt-6 p-4 sm:p-5">
        <div class="flex flex-col gap-3 md:flex-row md:items-end">
            <div class="md:w-52">
                <label for="period" class="label">Periode</label>
                <select id="period" name="period" class="input" data-dashboard-period>
                    @foreach ($periodOptions as $value => $label)
                        <option value="{{ $value }}" {{ $period === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary !min-h-0 md:w-28">Terapkan</button>
        </div>
        <div class="mt-3 grid grid-cols-2 gap-3 {{ $period === 'custom' ? '' : 'hidden' }}" data-custom-range>
            <div>
                <label for="from" class="label">Dari</label>
                <input id="from" name="from" type="date" value="{{ $from }}" class="input">
            </div>
            <div>
                <label for="to" class="label">Sampai</label>
                <input id="to" name="to" type="date" value="{{ $to }}" class="input">
            </div>
        </div>
    </form>

    <div class="card mt-6 p-5">
        <p class="text-sm font-medium text-brand-400">Total biaya ({{ $periodLabel }})</p>
        <p class="mt-2 text-2xl font-bold leading-none text-amber-700">Rp {{ number_format($total, 0, ',', '.') }}</p>
        <p class="mt-2 text-xs text-brand-400">Dipakai sebagai pengurang laba kotor pada laporan keuntungan periode yang sama.</p>
    </div>

    @if ($costs->isEmpty())
        <x-partials.empty-state
            icon="&#129518;"
            title="Belum ada biaya operasional"
            description="Catat biaya seperti listrik, gas, kemasan, atau biaya lain pada periode ini."
            actionHref="{{ route('admin.costs.create') }}"
            actionLabel="Tambah Biaya"
            class="mt-6" />
    @else
        <div class="card mt-6">
            <div class="table-scroll">
                <table class="w-full min-w-[720px] text-left text-sm">
                    <thead class="border-b border-brand-100 text-xs uppercase tracking-wide text-brand-400">
                        <tr>
                            <th class="px-4 py-3 font-medium">Nama</th>
                            <th class="px-4 py-3 font-medium">Kategori</th>
                            <th class="px-4 py-3 font-medium">Nominal</th>
                            <th class="px-4 py-3 font-medium">Tanggal</th>
                            <th class="px-4 py-3 font-medium">Dicatat oleh</th>
                            <th class="px-4 py-3 text-right font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-50">
                        @foreach ($costs as $cost)
                            <tr>
                                <td class="px-4 py-3">
                                    <span class="font-semibold text-brand-950">{{ $cost->name }}</span>
                                    @if ($cost->note)
                                        <span class="block text-xs text-brand-400">{{ \Illuminate\Support\Str::limit($cost->note, 80) }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-brand-950">{{ $categories[$cost->category] ?? $cost->category }}</td>
                                <td class="px-4 py-3 font-semibold text-brand-950">Rp {{ number_format((float) $cost->amount, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-brand-400">{{ $cost->incurred_at->timezone('Asia/Jakarta')->format('d M Y') }} WIB</td>
                                <td class="px-4 py-3 text-brand-400">{{ $cost->recorder?->name ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.costs.edit', $cost) }}" class="btn btn-secondary !min-h-0 px-3 py-1.5 text-xs">Edit</a>
                                        <form method="POST" action="{{ route('admin.costs.destroy', $cost) }}"
                                              onsubmit="return confirm('Hapus biaya {{ $cost->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger !min-h-0 border border-red-200 px-3 py-1.5 text-xs">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="divide-y divide-brand-50 border-t border-brand-100 md:hidden">
                @foreach ($costs as $cost)
                    <div class="p-4">
                        <div class="flex items-start justify-between gap-2">
                            <p class="font-semibold text-brand-950">{{ $cost->name }}</p>
                            <span class="font-semibold text-brand-950">Rp {{ number_format((float) $cost->amount, 0, ',', '.') }}</span>
                        </div>
                        <p class="mt-1 text-xs text-brand-400">
                            {{ $categories[$cost->category] ?? $cost->category }} &middot;
                            {{ $cost->incurred_at->timezone('Asia/Jakarta')->format('d M Y') }} WIB &middot;
                            dicatat oleh {{ $cost->recorder?->name ?? '-' }}
                        </p>
                        @if ($cost->note)
                            <p class="mt-1 text-xs text-brand-400">{{ \Illuminate\Support\Str::limit($cost->note, 120) }}</p>
                        @endif
                        <div class="mt-3 flex gap-2">
                            <a href="{{ route('admin.costs.edit', $cost) }}" class="btn btn-secondary !min-h-0 px-3 py-1.5 text-xs">Edit</a>
                            <form method="POST" action="{{ route('admin.costs.destroy', $cost) }}"
                                  onsubmit="return confirm('Hapus biaya {{ $cost->name }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger !min-h-0 border border-red-200 px-3 py-1.5 text-xs">Hapus</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-6">
            {{ $costs->links() }}
        </div>
    @endif
@endsection
