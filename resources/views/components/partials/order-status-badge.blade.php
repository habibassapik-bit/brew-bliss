@props(['status'])

@php
    $styles = [
        \App\Enums\OrderStatus::PendingConfirmation->value => 'badge-warning',
        \App\Enums\OrderStatus::AwaitingPayment->value => 'badge-warning',
        \App\Enums\OrderStatus::Processing->value => 'badge-neutral',
        \App\Enums\OrderStatus::ReadyPickup->value => 'badge-neutral',
        \App\Enums\OrderStatus::Shipping->value => 'badge-neutral',
        \App\Enums\OrderStatus::Completed->value => 'badge-success',
        \App\Enums\OrderStatus::Cancelled->value => 'badge-danger',
    ];
@endphp

<span class="badge {{ $styles[$status->value] ?? 'badge-neutral' }}">
    <span class="badge-dot"></span> {{ $status->label() }}
</span>
