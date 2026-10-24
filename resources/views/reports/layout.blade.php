<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title') · Akredia</title>
<style>
    @page { size: A4; margin: 18mm 16mm; }
    * { box-sizing: border-box; }
    body { font-family: 'Inter', 'Segoe UI', Arial, sans-serif; color: #0f172a; font-size: 12px; line-height: 1.55; margin: 0; background: #f1f5f9; }
    .sheet { max-width: 900px; margin: 24px auto; background: #fff; padding: 32px 36px; box-shadow: 0 4px 24px rgba(15,23,42,.08); border-radius: 8px; }
    header.top { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 3px solid #1f528c; padding-bottom: 10px; margin-bottom: 18px; }
    header.top h1 { font-size: 20px; margin: 0; color: #17406f; } header.top .meta { text-align: right; color: #64748b; font-size: 11px; }
    h2 { font-size: 15px; color: #17406f; margin: 22px 0 8px; } h3 { font-size: 13px; margin: 16px 0 4px; } h4 { font-size: 12px; margin: 12px 0 4px; }
    table { width: 100%; border-collapse: collapse; margin: 8px 0 14px; } th, td { border: 1px solid #cbd5e1; padding: 5px 7px; text-align: left; vertical-align: top; }
    th { background: #e8eef7; font-size: 11px; }
    .ok { color: #047857; font-weight: 700; } .bad { color: #be123c; font-weight: 700; } .muted { color: #64748b; }
    .kpis { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin: 10px 0; }
    .kpi { border: 1px solid #cbd5e1; border-radius: 8px; padding: 8px 10px; } .kpi b { display: block; font-size: 20px; } .kpi span { font-size: 10px; color: #64748b; text-transform: uppercase; }
    .print { position: fixed; right: 20px; top: 20px; background: #1f528c; color: #fff; border: 0; border-radius: 8px; padding: 8px 14px; font-weight: 700; cursor: pointer; }
    .narr { white-space: normal; } .narr p { margin: 0 0 6px; }
    @media print { body { background: #fff; } .sheet { box-shadow: none; margin: 0; padding: 0; max-width: none; } .print { display: none; } h2 { page-break-after: avoid; } }
</style>
</head>
<body>
<button class="print" onclick="window.print()">Cetak / Simpan PDF</button>
<div class="sheet">
    <header class="top">
        <div><div class="muted">{{ $institution->name }}</div><h1>@yield('title')</h1></div>
        <div class="meta">Akredia · IAPT 4.1<br>Dicetak {{ now()->translatedFormat('d F Y H:i') }}<br>{{ auth()->user()?->name }}</div>
    </header>
    @yield('content')
    <p class="muted" style="margin-top:24px;font-size:10px">AKI dan proyeksi skor adalah alat manajemen internal, bukan nilai resmi BAN-PT.</p>
</div>
</body>
</html>
