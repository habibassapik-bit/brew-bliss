@extends('layouts.admin')

@section('title', 'Detail Pelanggan')

@section('content')
    <a href="{{ route('admin.customers.index') }}" class="text-xs font-medium text-brand-500 hover:text-brand-900 hover:underline">&larr; Kembali ke Data Pelanggan</a>

    <div class="mt-3 grid gap-6 lg:grid-cols-3">
        <section class="card p-5 lg:col-span-1" aria-labelledby="info-pelanggan">
            <h2 id="info-pelanggan" class="heading-3">Informasi Pelanggan</h2>
            <dl class="mt-4 space-y-2 text-sm">
                <div class="flex justify-between gap-3">
                    <dt class="text-brand-400">Nama</dt>
                    <dd class="font-semibold text-brand-950">{{ $customer->name }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-brand-400">Email</dt>
                    <dd class="text-brand-950">{{ $customer->email }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-brand-400">Telepon</dt>
                    <dd class="text-brand-950">{{ $customer->phone ?? '-' }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-brand-400">Bergabung</dt>
                    <dd class="text-brand-950">{{ $customer->created_at->timezone('Asia/Jakarta')->format('d M Y') }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-brand-400">Jumlah Pesanan</dt>
                    <dd class="text-brand-950">{{ $customer->orders()->count() }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-brand-400">Total Nilai Dibayar</dt>
                    <dd class="font-bold text-brand-900">Rp {{ number_format($totalSpent, 0, ',', '.') }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-brand-400">Status Akun</dt>
                    <dd>
                        @if ($customer->is_active)
                            <span class="badge badge-success"><span class="badge-dot"></span> Aktif</span>
                        @else
                            <span class="badge badge-danger"><span class="badge-dot"></span> Nonaktif</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </section>

        <section class="card p-5 lg:col-span-2" aria-labelledby="riwayat-pesanan">
            <h2 id="riwayat-pesanan" class="heading-3">Riwayat Pesanan</h2>

            @if ($orders->isEmpty())
                <p class="mt-3 text-sm text-brand-400">Pelanggan ini belum memiliki pesanan.</p>
            @else
                <div class="table-scroll mt-4">
                    <table class="w-full min-w-[560px] text-left text-sm">
                        <thead class="border-b border-brand-100 text-xs uppercase tracking-wide text-brand-400">
                            <tr>
                                <th class="py-2 pr-4 font-medium">No. Pesanan</th>
                                <th class="py-2 pr-4 font-medium">Tanggal</th>
                                <th class="py-2 pr-4 font-medium">Total</th>
                                <th class="py-2 pr-4 font-medium">Status</th>
                                <th class="py-2 font-medium">Pembayaran</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-50">
                            @foreach ($orders as $order)
                                <tr>
                                    <td class="py-2.5 pr-4 font-mono font-semibold text-brand-950">{{ $order->order_number }}</td>
                                    <td class="py-2.5 pr-4 text-brand-400">{{ $order->created_at->timezone('Asia/Jakarta')->format('d M Y') }}</td>
                                    <td class="py-2.5 pr-4 font-semibold text-brand-900">Rp {{ number_format((float) $order->total, 0, ',', '.') }}</td>
                                    <td class="py-2.5 pr-4"><x-partials.order-status-badge :status="$order->status" /></td>
                                    <td class="py-2.5"><x-partials.payment-status-badge :status="$order->payment_status" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $orders->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection
