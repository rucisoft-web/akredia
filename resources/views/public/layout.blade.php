<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title') · {{ $institution->name }}</title>
<link rel="icon" href="{{ asset('favicon.svg') }}">
<style>
    :root { --b: #1f528c; --ink: #0f172a; --muted: #64748b; --line: #e2e8f0; }
    * { box-sizing: border-box; }
    body { margin: 0; font-family: 'Inter', 'Segoe UI', Arial, sans-serif; color: var(--ink); background: #f1f5f9; line-height: 1.55; }
    header { background: linear-gradient(135deg, #17406f, #2f68a8); color: #fff; padding: 22px 16px; }
    header .wrap, main { max-width: 900px; margin: 0 auto; }
    header small { color: #bcd3ee; text-transform: uppercase; letter-spacing: .1em; font-size: 11px; font-weight: 700; }
    header h1 { margin: 4px 0 0; font-size: 22px; }
    main { padding: 20px 16px 40px; }
    .card { background: #fff; border: 1px solid var(--line); border-radius: 14px; padding: 18px 20px; margin-bottom: 14px; box-shadow: 0 2px 10px rgba(15,23,42,.04); }
    .muted { color: var(--muted); font-size: 13px; }
    a { color: var(--b); }
    .btn { display: inline-block; background: var(--b); color: #fff; padding: 10px 16px; border-radius: 10px; text-decoration: none; font-weight: 700; border: 0; cursor: pointer; font-size: 14px; }
    table { width: 100%; border-collapse: collapse; font-size: 14px; } th, td { padding: 8px; border-bottom: 1px solid var(--line); text-align: left; vertical-align: top; }
    .q { padding: 12px 0; border-bottom: 1px solid var(--line); } .opts { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 6px; }
    .opts label { border: 1px solid var(--line); border-radius: 999px; padding: 6px 12px; font-size: 13px; cursor: pointer; }
    .opts input { margin-right: 4px; }
    textarea { width: 100%; border: 1px solid var(--line); border-radius: 10px; padding: 10px; font: inherit; }
    .ok { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; padding: 10px 14px; border-radius: 10px; margin-bottom: 14px; }
    footer { text-align: center; font-size: 12px; color: var(--muted); padding: 20px; }
</style>
</head>
<body>
<header><div class="wrap"><small>{{ $institution->name }}</small><h1>@yield('title')</h1></div></header>
<main>
@if ($institution->is_demo)
<div class="card" style="background:#fff7ed;border-color:#fed7aa;color:#7c2d12;font-size:14px"><b>DATA DEMO.</b> Halaman ini memakai data demo untuk menjelaskan fitur-fitur Akredia. Profil institusi diambil dari laman resminya; dokumen dan angka lainnya adalah ilustrasi, bukan data resmi {{ $institution->name }}.</div>
@endif
@yield('content')
</main>
<footer>Dikelola dengan Akredia · {{ $institution->website }}</footer>
</body>
</html>
