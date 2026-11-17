@props([
    'icon' => '&#128230;',
    'title',
    'description' => null,
    'actionHref' => null,
    'actionLabel' => null,
])

<div {{ $attributes->merge(['class' => 'empty-state']) }}>
    <span class="text-4xl" aria-hidden="true">{!! $icon !!}</span>
    <p class="mt-3 font-medium text-stone-700">{{ $title }}</p>
    @if ($description)
        <p class="mt-1 text-sm text-stone-500">{{ $description }}</p>
    @endif
    @if ($actionHref && $actionLabel)
        <a href="{{ $actionHref }}" class="btn btn-primary mt-5">{{ $actionLabel }}</a>
    @endif
</div>
