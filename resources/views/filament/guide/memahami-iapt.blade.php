@php
    $version = \App\Models\InstrumentVersion::query()->latest('id')->first();
    $variantId = $project?->instrument_variant_id ?? \App\Models\InstrumentVariant::query()->where('code', '3c')->value('id');
    $criteria = $version ? \App\Models\Criterion::query()->where('instrument_version_id', $version->id)->orderBy('number')
        ->with(['indicators' => fn ($q) => $q->orderBy('number')->with(['subIndicators' => fn ($q) => $q->where('instrument_variant_id', $variantId)->orderBy('sort')])])->get() : collect();
    $targets = \App\Services\InstrumentImporter::TARGETS;
@endphp
<x-filament-panels::page>
<div class="ak-guide">
    <div class="ak-guide-hero">
        <h2>IAPT 4.1 dalam satu halaman</h2>
        <p>Instrumen Akreditasi Perguruan Tinggi 4.1 ditetapkan dengan Peraturan BAN-PT No. 35 Tahun 2025. Instrumen ini menggantikan sembilan kriteria lama dengan empat kriteria yang menilai mutu sebagai siklus: dari masukan, proses, luaran, sampai dampak. Penilaian setiap sub-indikator bersifat biner (memenuhi atau tidak), dan status akhir hanya dua: Terakreditasi atau Terakreditasi Unggul.</p>
    </div>

    <div class="ak-guide-grid">
        <div class="ak-card"><h3>Biner, bukan skala 0–4</h3><p>Setiap sub-indikator dinilai memenuhi atau tidak terhadap deskriptor jalur. Proyeksi skor adalah bobot sub-indikator yang memenuhi dibagi total bobot.</p></div>
        <div class="ak-card"><h3>Syarat perlu</h3><p>Sub-indikator bertanda syarat perlu harus memenuhi. Satu syarat perlu gagal menggugurkan status walaupun skor total tinggi. Akredia menandainya di heatmap dan gerbang pengajuan.</p></div>
        <div class="ak-card"><h3>Deskriptor dan kata kunci</h3><p>Deskriptor dipecah menjadi kata kunci. Setiap kata kunci perlu didukung bukti valid dan disebut dalam LED. Inilah dasar kertas kerja analisis kesenjangan.</p></div>
        <div class="ak-card"><h3>LED maksimal 1.500 kata</h3><p>Narasi per sub-indikator ditulis langsung di SAPTO, mengikuti PPEPP serta analisis akar masalah, faktor pendukung, faktor penghambat, dan tindak lanjut; boleh memuat tautan bukti publik.</p></div>
    </div>

    @foreach ($criteria as $criterion)
        <div class="ak-card ak-crit">
            <h3>Kriteria {{ $criterion->number }} · {{ $criterion->name }}</h3>
            <p class="ak-muted">{{ $criterion->description }}</p>
            <table>
                @foreach ($criterion->indicators as $ind)
                    <tr>
                        <td style="width:3rem"><b>{{ $ind->number }}</b></td>
                        <td style="width:6rem"><span class="ak-chip">{{ $ind->quality_target?->getLabel() }}</span></td>
                        <td>{{ $ind->statement }}
                            @foreach ($ind->subIndicators as $sub)
                                @if ($sub->is_prerequisite_accredited || $sub->is_prerequisite_excellent)
                                    <span class="ak-pill {{ $sub->is_prerequisite_accredited ? 'red' : 'gray' }}">{{ $sub->code }} syarat perlu {{ $sub->is_prerequisite_accredited ? 'Terakreditasi' : 'Unggul' }}</span>
                                @endif
                            @endforeach
                        </td>
                        <td style="width:9rem" class="ak-muted">{{ $ind->pic_suggestion }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endforeach

    <div class="ak-card">
        <h3>Rasio kuantitatif yang dipantau</h3>
        <ul>
            <li><b>RMDT</b> rasio mahasiswa terhadap dosen tetap; <b>PDTT</b> proporsi dosen tidak tetap; <b>% doktor</b>; <b>DTJA</b> dosen tetap berjabatan akademik.</li>
            <li><b>RPL</b> penurunan lulusan; <b>PKMTK</b> kelulusan tepat masa tempuh; <b>PK2MTK</b> kelulusan dalam dua kali masa tempuh.</li>
            <li><b>PPM</b> penurunan mahasiswa baru; <b>PMAP</b> mahasiswa berprestasi; <b>PLLK</b> lulusan bekerja/studi lanjut/wirausaha ≤ 1 tahun.</li>
            <li><b>% prodi terakreditasi</b> dan proporsi prodi Unggul.</li>
        </ul>
        <p class="ak-muted">Ambang setiap rasio disimpan per varian dan per jalur di Pustaka Instrumen. Teks resmi dan ambang wajib dicocokkan dengan Lampiran 3 varian PT sebelum pengajuan.</p>
        <a class="more" href="{{ $l['map'] }}">Telusuri peta instrumen lengkap →</a>
    </div>
</div>
</x-filament-panels::page>
