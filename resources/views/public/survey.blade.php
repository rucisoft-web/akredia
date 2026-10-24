@extends('public.layout')
@section('title', $survey->title)
@section('content')
@if (session('status'))<div class="ok">{{ session('status') }}</div>@endif
@if (! $survey->is_open)
    <div class="card"><p>Kuesioner ini sudah ditutup. Terima kasih atas perhatian Anda.</p></div>
@else
<form class="card" method="post" action="{{ route('survei.submit', $survey->public_token) }}">
    @csrf
    <p class="muted">Kuesioner kepuasan {{ strtolower($survey->stakeholder->getLabel()) }} terhadap tata pamong {{ $institution->name }} tahun {{ $survey->year }}. Jawaban bersifat anonim.</p>
    @foreach (\App\Models\Survey::QUESTIONS as $i => $q)
        <div class="q">
            <b>{{ $i + 1 }}. {{ $q }}</b>
            <div class="opts">
                @foreach ([4 => 'Sangat Baik', 3 => 'Baik', 2 => 'Cukup', 1 => 'Kurang Baik'] as $v => $label)
                    <label><input type="radio" name="answers[{{ $i }}]" value="{{ $v }}" required @checked(old("answers.$i") == $v)>{{ $label }}</label>
                @endforeach
            </div>
            @error("answers.$i")<div class="muted" style="color:#be123c">Wajib diisi.</div>@enderror
        </div>
    @endforeach
    <div class="q"><b>Saran atau masukan (opsional)</b><textarea name="comment" rows="3">{{ old('comment') }}</textarea></div>
    <p><button class="btn" type="submit">Kirim jawaban</button></p>
</form>
@endif
@endsection
