@extends('layouts.admin')

@section('title', 'Log Aktivitas')

@section('content')
    @php
        /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator<int, \App\Models\ActivityLog> $logs */

        $actionLabels = [
            'product.created' => 'Produk dibuat',
            'product.updated' => 'Produk diperbarui',
            'product.deactivated' => 'Produk dinonaktifkan',
            'category.created' => 'Kategori dibuat',
            'category.updated' => 'Kategori diperbarui',
            'category.deleted' => 'Kategori dihapus',
            'order.status_changed' => 'Status pesanan diubah',
            'order.cancelled' => 'Pesanan dibatalkan',
            'payment.approved' => 'Pembayaran disetujui',
            'payment.rejected' => 'Pembayaran ditolak',
            'voucher.created' => 'Voucher dibuat',
            'voucher.updated' => 'Voucher diperbarui',
            'voucher.deactivated' => 'Voucher dinonaktifkan',
            'cost.created' => 'Biaya operasional ditambah',
            'cost.updated' => 'Biaya operasional diperbarui',
            'cost.deleted' => 'Biaya operasional dihapus',
            'report.exported' => 'Laporan diekspor',
        ];

        $subjectLinks = [
            'product' => 'admin.products.show',
            'order' => 'admin.orders.show',
            'payment' => 'admin.payments.index',
            'voucher' => 'admin.vouchers.index',
            'category' => 'admin.categories.index',
        ];
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="heading-1">Log Aktivitas</h1>
            <p class="mt-1 text-sm text-brand-400">Riwayat tindakan penting admin, terbaru terlebih dahulu.</p>
        </div>
    </div>

    @if ($logs->isEmpty())
        <x-partials.empty-state
            icon="&#128220;"
            title="Belum ada aktivitas tercatat"
            description="Tindakan admin seperti mengubah produk atau memverifikasi pembayaran akan muncul di sini."
            class="mt-6" />
    @else
        <div class="card mt-6">
            <div class="table-scroll">
                <table class="w-full min-w-[760px] text-left text-sm">
                    <thead class="border-b border-brand-100 text-xs uppercase tracking-wide text-brand-400">
                        <tr>
                            <th class="px-4 py-3 font-medium">Waktu</th>
                            <th class="px-4 py-3 font-medium">Admin</th>
                            <th class="px-4 py-3 font-medium">Aktivitas</th>
                            <th class="px-4 py-3 font-medium">Deskripsi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-50">
                        @foreach ($logs as $log)
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap text-brand-400">{{ $log->created_at->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB</td>
                                <td class="px-4 py-3 font-semibold text-brand-950">{{ $log->user?->name ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <span class="badge badge-neutral"><span class="badge-dot"></span> {{ $actionLabels[$log->action] ?? $log->action }}</span>
                                </td>
                                <td class="px-4 py-3 text-brand-950">
                                    {{ \Illuminate\Support\Arr::get($log->properties ?? [], 'description', '-') }}
                                    @php
                                        $subjectBase = \Illuminate\Support\Str::afterLast($log->subject_type ?? '', '\\');
                                    @endphp
                                    @if (isset($subjectLinks[$subjectBase]) && $log->subject_id)
                                        <a href="{{ route($subjectLinks[$subjectBase], $log->subject_id) }}" class="ml-1 text-xs text-brand-400 hover:underline">Lihat</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="divide-y divide-brand-50 border-t border-brand-100 md:hidden">
                @foreach ($logs as $log)
                    <div class="p-4">
                        <div class="flex items-start justify-between gap-2">
                            <p class="font-semibold text-brand-950">{{ $log->user?->name ?? '-' }}</p>
                            <span class="text-xs text-brand-400">{{ $log->created_at->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB</span>
                        </div>
                        <span class="badge badge-neutral mt-1"><span class="badge-dot"></span> {{ $actionLabels[$log->action] ?? $log->action }}</span>
                        <p class="mt-2 text-sm text-brand-950">{{ \Illuminate\Support\Arr::get($log->properties ?? [], 'description', '-') }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-6">
            {{ $logs->links() }}
        </div>
    @endif
@endsection
