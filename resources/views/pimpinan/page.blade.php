<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Akredia untuk Pimpinan Perguruan Tinggi</title>
<meta name="description" content="Bagaimana Akredia membantu rektor dan pimpinan menyiapkan akreditasi institusi dengan IAPT 4.1.">
<link rel="icon" href="{{ asset('favicon.svg') }}">
<link rel="stylesheet" href="{{ asset('css/pimpinan.css') }}?v={{ @filemtime(public_path('css/pimpinan.css')) }}">
<style>
    * { box-sizing: border-box; }
    body { margin: 0; font-family: 'Inter', 'Segoe UI', Arial, sans-serif; background: #f1f5f9; }
    .top { display: flex; justify-content: space-between; align-items: center; max-width: 1080px; margin: 0 auto; padding: 16px; }
    .top a { color: #17406f; font-weight: 700; text-decoration: none; }
    .brand { display: flex; align-items: center; gap: 8px; font-size: 18px; }
    main { padding: 0 16px 40px; }
    footer { text-align: center; font-size: 12px; color: #64748b; padding: 24px 16px 40px; }
</style>
</head>
<body>
<div class="top">
    <a class="brand" href="{{ route('pimpinan') }}"><img src="{{ asset('favicon.svg') }}" width="28" height="28" alt=""> Akredia</a>
    <a href="{{ $loginUrl }}">Masuk</a>
</div>
<main>@include('pimpinan.content', ['standalone' => true])</main>
<footer>Akredia · persiapan akreditasi institusi IAPT 4.1 · Rucisoft. AKI dan proyeksi skor adalah alat manajemen internal, bukan prediksi resmi BAN-PT.</footer>
</body>
</html>
