<x-filament-panels::page>
<div class="ak-report-grid">
    @foreach ($reports as [$title, $desc, $url, $label])
        <div class="ak-card">
            <h3>{{ $title }}</h3>
            <p>{{ $desc }}</p>
            <a class="more" href="{{ $url }}" target="_blank">{{ $label }} →</a>
        </div>
    @endforeach
</div>
</x-filament-panels::page>
