@if ($errors->any())
    <x-partials.status-banner type="error" class="mb-4">
        <p class="font-semibold">Terjadi kesalahan:</p>
        <ul class="mt-1 list-inside list-disc space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-partials.status-banner>
@endif
