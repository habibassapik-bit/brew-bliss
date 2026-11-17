@props([
    'label',
    'value',
    'tone' => 'neutral',
    'hint' => null,
])

@php
    $tones = [
        'neutral' => 'text-brand-950',
        'success' => 'text-emerald-700',
        'warning' => 'text-amber-700',
        'danger' => 'text-red-700',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'card p-5']) }}>
    <p class="text-sm font-medium text-brand-400">{{ $label }}</p>
    <p class="mt-2 text-2xl font-bold leading-none {{ $tones[$tone] ?? $tones['neutral'] }}">{{ $value }}</p>
    @if ($hint)
        <p class="mt-2 text-xs text-brand-400">{{ $hint }}</p>
    @endif
</div>
