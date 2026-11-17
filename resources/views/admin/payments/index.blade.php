@extends('layouts.admin')

@section('title', 'Verifikasi Pembayaran')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="heading-1">Verifikasi Pembayaran</h1>
            <p class="mt-1 text-sm text-brand-400">Pembayaran manual hanya dianggap berhasil setelah diverifikasi.</p>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.payments.index') }}" class="card mt-6 flex flex-col gap-3 p-4 sm:flex-row sm:items-center">
        <select name="status" aria-label="Filter status pembayaran" class="input !mt-0 sm:w-56">
            <option value="">Semua status</option>
            @foreach ($statuses as $paymentStatus)
                <option value="{{ $paymentStatus->value }}" {{ $statusFilter === $paymentStatus->value ? 'selected' : '' }}>{{ $paymentStatus->label() }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-secondary !min-h-0 sm:w-24">Filter</button>
    </form>

    @if ($payments->isEmpty())
        <x-partials.empty-state
            icon="&#128181;"
            title="Belum ada pembayaran"
            description="Daftar pembayaran akan muncul di sini setelah customer checkout."
            class="mt-6" />
    @else
        <div class="mt-6 space-y-4">
            @foreach ($payments as $payment)
                @php $order = $payment->order; @endphp
                <article class="card p-4 sm:p-5">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-mono text-sm font-bold text-brand-950">{{ $order?->order_number }}</p>
                            <p class="mt-0.5 text-xs text-brand-400">
                                {{ $payment->method->label() }} &middot; Pelanggan: {{ $order?->customer_name ?? '-' }}
                                @if ($order)
                                    &middot; {{ $payment->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }}
                                @endif
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <x-partials.payment-status-badge :status="$payment->status" />
                            @if ($order)
                                <x-partials.order-status-badge :status="$order->status" />
                            @endif
                        </div>
                    </div>

                    <div class="mt-3 grid gap-3 text-sm sm:grid-cols-3">
                        <p><span class="text-brand-400">Jumlah:</span> <span class="font-bold text-brand-900">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</span></p>
                        <p><span class="text-brand-400">Pengirim:</span> <span class="text-brand-950">{{ $payment->payer_name ?? '-' }}</span></p>
                        <p><span class="text-brand-400">Ref:</span> <span class="text-brand-950">{{ $payment->reference_number ?? '-' }}</span></p>
                    </div>

                    @if ($payment->rejection_reason)
                        <p class="mt-2 rounded-xl bg-red-50 p-2 text-xs text-red-700">Alasan penolakan: {{ $payment->rejection_reason }}</p>
                    @endif

                    <div class="mt-4 flex flex-wrap items-center gap-3 border-t border-dashed border-brand-100 pt-3">
                        @if ($payment->proof_path)
                            <a href="{{ route('payments.proof', $order) }}" target="_blank" rel="noopener"
                               class="btn btn-secondary !min-h-0 px-4 py-2 text-xs">&#128247; Lihat Bukti</a>
                        @else
                            <span class="text-xs text-brand-400">Belum ada bukti diunggah.</span>
                        @endif
                        @if ($order)
                            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-ghost !min-h-0 px-3 py-2 text-xs">Detail Pesanan</a>
                        @endif

                        @if (in_array($payment->status, [\App\Enums\PaymentStatus::Unpaid, \App\Enums\PaymentStatus::PendingVerification], true))
                            <div class="ml-auto flex flex-wrap items-end gap-2">
                                <form method="POST" action="{{ route('admin.payments.approve', $payment) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-primary !min-h-0 px-4 py-2 text-xs"
                                            onclick="return confirm('Setujui pembayaran pesanan {{ $order?->order_number }}?')">
                                        Setujui
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.payments.reject', $payment) }}" class="flex items-end gap-2">
                                    @csrf
                                    <input type="text" name="rejection_reason" required minlength="5" maxlength="255"
                                           placeholder="Alasan penolakan" aria-label="Alasan penolakan"
                                           class="input !mt-0 !min-h-0 w-44 px-2 py-1.5 text-xs">
                                    <button type="submit" class="btn btn-danger !min-h-0 border border-red-200 px-4 py-2 text-xs"
                                            onclick="return confirm('Tolak pembayaran pesanan {{ $order?->order_number }}?')">
                                        Tolak
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $payments->links() }}
        </div>
    @endif
@endsection
