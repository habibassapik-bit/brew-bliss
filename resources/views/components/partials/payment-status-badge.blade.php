@props(['status'])

@php
    $styles = [
        \App\Enums\PaymentStatus::Unpaid->value => 'badge-danger',
        \App\Enums\PaymentStatus::PendingVerification->value => 'badge-warning',
        \App\Enums\PaymentStatus::Paid->value => 'badge-success',
        \App\Enums\PaymentStatus::Rejected->value => 'badge-danger',
        \App\Enums\PaymentStatus::Refunded->value => 'badge-neutral',
    ];
@endphp

<span class="badge {{ $styles[$status->value] ?? 'badge-neutral' }}">
    <span class="badge-dot"></span> {{ $status->label() }}
</span>
