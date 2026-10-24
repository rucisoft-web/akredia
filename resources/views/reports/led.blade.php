@extends('reports.layout')
@section('title', 'Laporan Evaluasi Diri')
@section('content')
<p><b>{{ $project->name }}</b> · jalur {{ $project->track->getLabel() }} · status {{ $project->state->getLabel() }}</p>
@foreach ($items->groupBy(fn ($w) => $w->subIndicator->indicator->criterion->number) as $num => $group)
    <h2>Kriteria {{ $num }} · {{ $group->first()->subIndicator->indicator->criterion->name }}</h2>
    @foreach ($group->groupBy(fn ($w) => $w->subIndicator->indicator->number) as $indNum => $subs)
        <h3>Indikator {{ $indNum }}</h3>
        <p class="muted"><i>{{ $subs->first()->subIndicator->indicator->statement }}</i></p>
        @foreach ($subs as $w)
            <h4>{{ $w->subIndicator->code }}. {{ $w->subIndicator->statement }}</h4>
            <div class="narr">{!! $w->ledSection?->body ? $led->render($w->ledSection) : '<p class="muted">(Narasi belum ditulis.)</p>' !!}</div>
            <p class="muted" style="font-size:10px">{{ $w->ledSection?->word_count ?? 0 }} kata · {{ $w->ledSection?->state->getLabel() }}</p>
        @endforeach
    @endforeach
@endforeach
@endsection
