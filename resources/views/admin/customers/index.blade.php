@extends('layouts.admin')

@section('title', 'Data Pelanggan')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="heading-1">Data Pelanggan</h1>
            <p class="mt-1 text-sm text-brand-400">Data pelanggan dibatasi untuk keperluan operasional toko.</p>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.customers.index') }}" class="card mt-6 flex flex-col gap-3 p-4 sm:flex-row sm:items-center">
        <input type="search" name="q" value="{{ $search }}" placeholder="Cari nama, email, atau telepon..." aria-label="Cari pelanggan" class="input !mt-0 flex-1">
        <button type="submit" class="btn btn-secondary !min-h-0 sm:w-24">Cari</button>
    </form>

    @if ($customers->isEmpty())
        <x-partials.empty-state
            icon="&#128100;"
            title="Pelanggan tidak ditemukan"
            description="Belum ada pelanggan terdaftar, atau tidak ada yang cocok dengan pencarian."
            class="mt-6" />
    @else
        <div class="card mt-6">
            <div class="table-scroll">
                <table class="w-full min-w-[760px] text-left text-sm">
                    <thead class="border-b border-brand-100 text-xs uppercase tracking-wide text-brand-400">
                        <tr>
                            <th class="px-4 py-3 font-medium">Nama</th>
                            <th class="px-4 py-3 font-medium">Email</th>
                            <th class="px-4 py-3 font-medium">Telepon</th>
                            <th class="px-4 py-3 font-medium">Bergabung</th>
                            <th class="px-4 py-3 font-medium">Pesanan</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 text-right font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-50">
                        @foreach ($customers as $customer)
                            <tr>
                                <td class="px-4 py-3 font-semibold text-brand-950">{{ $customer->name }}</td>
                                <td class="px-4 py-3 text-brand-400">{{ $customer->email }}</td>
                                <td class="px-4 py-3 text-brand-950">{{ $customer->phone ?? '-' }}</td>
                                <td class="px-4 py-3 text-brand-400">{{ $customer->created_at->timezone('Asia/Jakarta')->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-brand-950">{{ $customer->orders_count }}</td>
                                <td class="px-4 py-3">
                                    @if ($customer->is_active)
                                        <span class="badge badge-success"><span class="badge-dot"></span> Aktif</span>
                                    @else
                                        <span class="badge badge-danger"><span class="badge-dot"></span> Nonaktif</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-ghost !min-h-0 px-3 py-1.5 text-xs">Detail</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="divide-y divide-brand-50 border-t border-brand-100 md:hidden">
                @foreach ($customers as $customer)
                    <div class="p-4">
                        <div class="flex items-start justify-between gap-2">
                            <p class="font-semibold text-brand-950">{{ $customer->name }}</p>
                            @if ($customer->is_active)
                                <span class="badge badge-success"><span class="badge-dot"></span> Aktif</span>
                            @else
                                <span class="badge badge-danger"><span class="badge-dot"></span> Nonaktif</span>
                            @endif
                        </div>
                        <p class="mt-1 text-xs text-brand-400">{{ $customer->email }}</p>
                        <p class="text-xs text-brand-400">{{ $customer->phone ?? '-' }} &middot; Bergabung {{ $customer->created_at->timezone('Asia/Jakarta')->format('d M Y') }} &middot; {{ $customer->orders_count }} pesanan</p>
                        <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-secondary !min-h-0 mt-3 px-4 py-1.5 text-xs">Detail</a>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-6">
            {{ $customers->links() }}
        </div>
    @endif
@endsection
