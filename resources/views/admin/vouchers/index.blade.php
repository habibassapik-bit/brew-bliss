@extends('layouts.admin')

@section('title', 'Voucher & Promo')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="heading-1">Voucher &amp; Promo</h1>
            <p class="mt-1 text-sm text-brand-400">Kelola kode diskon untuk checkout pelanggan.</p>
        </div>
        <a href="{{ route('admin.vouchers.create') }}" class="btn btn-primary">Tambah Voucher</a>
    </div>

    @if ($vouchers->isEmpty())
        <x-partials.empty-state
            icon="&#127873;"
            title="Belum ada voucher"
            description="Buat voucher pertama untuk promo toko."
            actionHref="{{ route('admin.vouchers.create') }}"
            actionLabel="Tambah Voucher"
            class="mt-6" />
    @else
        <div class="card mt-6">
            <div class="table-scroll">
                <table class="w-full min-w-[820px] text-left text-sm">
                    <thead class="border-b border-brand-100 text-xs uppercase tracking-wide text-brand-400">
                        <tr>
                            <th class="px-4 py-3 font-medium">Kode</th>
                            <th class="px-4 py-3 font-medium">Diskon</th>
                            <th class="px-4 py-3 font-medium">Min. Beli</th>
                            <th class="px-4 py-3 font-medium">Kuota</th>
                            <th class="px-4 py-3 font-medium">Periode</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 text-right font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-50">
                        @foreach ($vouchers as $voucher)
                            <tr>
                                <td class="px-4 py-3 font-mono font-bold text-brand-950">{{ $voucher->code }}</td>
                                <td class="px-4 py-3 text-brand-950">
                                    @if ($voucher->type === \App\Enums\VoucherType::Percent)
                                        {{ rtrim(rtrim((string) $voucher->value, '0'), '.') }}%
                                    @else
                                        Rp {{ number_format((float) $voucher->value, 0, ',', '.') }}
                                    @endif
                                    @if ($voucher->max_discount !== null)
                                        <span class="text-xs text-brand-400">(maks Rp {{ number_format((float) $voucher->max_discount, 0, ',', '.') }})</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-brand-400">Rp {{ number_format((float) $voucher->min_purchase, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-brand-950">
                                    {{ $voucher->quota === null ? 'Tanpa batas' : $voucher->used_count.'/'.$voucher->quota }}
                                </td>
                                <td class="px-4 py-3 text-xs text-brand-400">
                                    {{ $voucher->starts_at?->timezone('Asia/Jakarta')->format('d M y') ?? '-' }} &ndash;
                                    {{ $voucher->ends_at?->timezone('Asia/Jakarta')->format('d M y') ?? '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    @if ($voucher->is_active)
                                        <span class="badge badge-success"><span class="badge-dot"></span> Aktif</span>
                                    @else
                                        <span class="badge badge-neutral"><span class="badge-dot"></span> Nonaktif</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.vouchers.edit', $voucher) }}" class="btn btn-secondary !min-h-0 px-3 py-1.5 text-xs">Edit</a>
                                        @if ($voucher->is_active)
                                            <form method="POST" action="{{ route('admin.vouchers.destroy', $voucher) }}"
                                                  onsubmit="return confirm('Nonaktifkan voucher {{ $voucher->code }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger !min-h-0 border border-red-200 px-3 py-1.5 text-xs">Nonaktifkan</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="divide-y divide-brand-50 border-t border-brand-100 md:hidden">
                @foreach ($vouchers as $voucher)
                    <div class="p-4">
                        <div class="flex items-start justify-between gap-2">
                            <p class="font-mono font-bold text-brand-950">{{ $voucher->code }}</p>
                            @if ($voucher->is_active)
                                <span class="badge badge-success"><span class="badge-dot"></span> Aktif</span>
                            @else
                                <span class="badge badge-neutral"><span class="badge-dot"></span> Nonaktif</span>
                            @endif
                        </div>
                        <p class="mt-1 text-sm text-brand-950">
                            @if ($voucher->type === \App\Enums\VoucherType::Percent)
                                Diskon {{ rtrim(rtrim((string) $voucher->value, '0'), '.') }}%
                            @else
                                Diskon Rp {{ number_format((float) $voucher->value, 0, ',', '.') }}
                            @endif
                        </p>
                        <p class="text-xs text-brand-400">
                            Min. Rp {{ number_format((float) $voucher->min_purchase, 0, ',', '.') }} &middot;
                            {{ $voucher->quota === null ? 'Tanpa batas' : 'Kuota '.$voucher->used_count.'/'.$voucher->quota }}
                        </p>
                        <div class="mt-3 flex gap-2">
                            <a href="{{ route('admin.vouchers.edit', $voucher) }}" class="btn btn-secondary !min-h-0 px-3 py-1.5 text-xs">Edit</a>
                            @if ($voucher->is_active)
                                <form method="POST" action="{{ route('admin.vouchers.destroy', $voucher) }}"
                                      onsubmit="return confirm('Nonaktifkan voucher {{ $voucher->code }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger !min-h-0 border border-red-200 px-3 py-1.5 text-xs">Nonaktifkan</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-6">
            {{ $vouchers->links() }}
        </div>
    @endif
@endsection
