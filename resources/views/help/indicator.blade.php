{{-- Rujukan resmi satu indikator: $ind dari database/data/iapt41-resmi-3c.php --}}
<div class="ak-help-indbody">
    <p class="ak-help-meta">Kriteria <b>{{ $ind['kriteria'] }}</b> · Sasaran mutu <b>{{ $ind['sasaran'] }}</b></p>
    <p class="ak-help-pre">{{ $ind['indikator'] }}</p>
    @php($pairs = collect($ind['terakreditasi'])->map(fn ($a, $i) => [$a, $ind['unggul'][$i] ?? null]))
    @foreach ($pairs as [$t, $u])
        <div class="ak-help-aspect">
            <h4>{{ $t['label'] ? 'Aspek '.$t['label'] : 'Aspek penilaian' }}</h4>
            <p class="ak-help-pre">{{ preg_replace('/^[A-H]\.\s*/', '', $t['aspek']) }}</p>
            @if ($t['sumber'])<p class="ak-help-meta"><b>Sumber data:</b> {{ $t['sumber'] }}</p>@endif
            <div class="ak-help-levels">
                <div class="lvl t">
                    <span>Terakreditasi @if ($t['syarat_perlu'])<em>syarat perlu</em>@endif</span>
                    <p class="ak-help-pre">{{ $t['deskripsi'] }}</p>
                </div>
                @if ($u)
                    <div class="lvl u">
                        <span>Unggul @if ($u['syarat_perlu'])<em>syarat perlu</em>@endif</span>
                        <p class="ak-help-pre">{{ $u['deskripsi'] }}</p>
                    </div>
                @endif
            </div>
        </div>
    @endforeach
    @if ($ind['panduan_led'])
        <div class="ak-help-led">
            <h4>Petunjuk Panduan LED (Lampiran 4a)</h4>
            <p class="ak-help-pre">{{ $ind['panduan_led'] }}</p>
        </div>
    @endif
    @if ($ind['bukti_kriteria'])
        <div class="ak-help-led">
            <h4>Contoh bukti yang diminta untuk kriteria ini</h4>
            <p class="ak-help-pre">{{ $ind['bukti_kriteria'] }}</p>
        </div>
    @endif
    @if ($ind['justifikasi'])
        <p class="ak-help-meta"><b>Dasar regulasi:</b> {{ preg_replace('/\s+/', ' ', $ind['justifikasi']) }}</p>
    @endif
</div>
