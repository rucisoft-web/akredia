@php($guestUrl = \App\Http\Controllers\DemoController::guestUrl())
<div class="ak-login-links">
    @if ($guestUrl)
        <a class="ak-login-guest" href="{{ $guestUrl }}">Masuk sebagai tamu (lihat demo UNJ)</a>
        <p>Tanpa akun. Tamu hanya dapat melihat data demo, tidak dapat mengubahnya.</p>
    @endif
    <a class="ak-login-pimpinan" href="{{ route('pimpinan') }}">Akredia untuk Pimpinan Perguruan Tinggi &rarr;</a>
</div>
