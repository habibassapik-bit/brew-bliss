@props(['type' => 'success', 'message' => null])

@php
    $styles = [
        'success' => ['class' => 'border-emerald-200 bg-emerald-50 text-emerald-800', 'role' => 'status', 'icon' => '&#10003;'],
        'error' => ['class' => 'border-red-200 bg-red-50 text-red-700', 'role' => 'alert', 'icon' => '&#33;'],
        'info' => ['class' => 'border-brand-100 bg-brand-50 text-brand-900', 'role' => 'status', 'icon' => 'i'],
    ];

    $style = $styles[$type] ?? $styles['info'];
@endphp

<div {{ $attributes->merge(['role' => $style['role']]) }}
     class="flex items-start gap-2.5 rounded-xl border px-4 py-3 text-sm {{ $style['class'] }}">
    <span class="mt-0.5 grid h-5 w-5 shrink-0 place-items-center rounded-full border border-current/30 text-[11px] font-bold"
          aria-hidden="true">{!! $style['icon'] !!}</span>
    <div class="min-w-0">{{ $message ?? $slot }}</div>
</div>
