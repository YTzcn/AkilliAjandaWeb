<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ajanda raporu {{ $dateFrom }} – {{ $dateTo }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,600;0,700;1,500&family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,600;1,9..40,400&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink: #1a1814;
            --ink-soft: #4a453c;
            --paper: #f7f4ee;
            --line: rgba(26, 24, 20, 0.12);
            --accent: #c45c3e;
            --teal: #2d6a6a;
        }
        * { box-sizing: border-box; }
        body {
            font-family: "DM Sans", system-ui, sans-serif;
            font-size: 11px;
            line-height: 1.45;
            margin: 0;
            padding: 1.25rem 1.5rem 2rem;
            color: var(--ink);
            background: var(--paper);
        }
        .mast {
            border-bottom: 2px solid var(--ink);
            padding-bottom: 0.75rem;
            margin-bottom: 1.25rem;
        }
        .mast h1 {
            font-family: "Cormorant Garamond", Georgia, serif;
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0 0 0.25rem;
            letter-spacing: -0.02em;
        }
        .mast .range {
            font-size: 0.85rem;
            color: var(--ink-soft);
            letter-spacing: 0.04em;
        }
        .summary {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem 2rem;
            margin-bottom: 1.5rem;
            padding: 0.75rem 0;
            font-size: 0.8rem;
            border-bottom: 1px solid var(--line);
        }
        .summary strong { color: var(--teal); }
        .insights {
            margin: 0 0 1.25rem;
            padding: 0.65rem 0.85rem;
            background: rgba(45, 106, 106, 0.08);
            border-left: 3px solid var(--teal);
            font-size: 0.78rem;
            line-height: 1.5;
        }
        .insights ul { margin: 0.35rem 0 0; padding-left: 1.1rem; }
        .insights li { margin-bottom: 0.25rem; }
        h2 {
            font-family: "Cormorant Garamond", Georgia, serif;
            font-size: 1.2rem;
            font-weight: 600;
            margin: 1.25rem 0 0.5rem;
            color: var(--ink);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.25rem;
            page-break-inside: avoid;
        }
        th, td {
            border: 1px solid var(--line);
            padding: 0.4rem 0.55rem;
            text-align: left;
        }
        th {
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--ink-soft);
            background: #ebe6dc;
        }
        tbody tr:nth-child(even) { background: rgba(255, 255, 255, 0.5); }
        @media print {
            body { margin: 0.4cm; padding: 0; background: #fff; }
            a { color: inherit; text-decoration: none; }
        }
    </style>
</head>
<body>
    <header class="mast">
        <h1>Ajanda dönem raporu</h1>
        <p class="range">{{ $dateFrom }} — {{ $dateTo }}</p>
    </header>

    <section class="summary">
        <span><strong>Görev</strong> · toplam {{ $report->taskTotal }} (tamamlanan {{ $report->taskCompleted }}, bekleyen {{ $report->taskPending }})</span>
        <span><strong>Etkinlik</strong> · kesişen {{ $report->eventTotal }}</span>
        <span><strong>Tamamlanma</strong> · %{{ $report->insights->completionRatePercent }}</span>
    </section>

    @if(count($report->insights->narrativeBullets))
        <div class="insights">
            <strong>Öne çıkanlar</strong>
            <ul>
                @foreach(array_slice($report->insights->narrativeBullets, 0, 4) as $b)
                    <li>{{ $b }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <h2>Görevler</h2>
    <table>
        <thead>
            <tr><th>Başlık</th><th>Son tarih</th><th>Durum</th><th>Tamam</th><th>Öncelik</th></tr>
        </thead>
        <tbody>
            @foreach($report->tasks as $t)
                <tr>
                    <td>{{ $t->title }}</td>
                    <td>{{ $t->due_date?->format('d.m.Y H:i') ?? '' }}</td>
                    <td>{{ $t->status }}</td>
                    <td>{{ $t->is_completed ? 'Evet' : 'Hayır' }}</td>
                    <td>{{ $t->priority }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Etkinlikler</h2>
    <table>
        <thead>
            <tr><th>Başlık</th><th>Başlangıç</th><th>Bitiş</th><th>Konum</th></tr>
        </thead>
        <tbody>
            @foreach($report->events as $e)
                <tr>
                    <td>{{ $e->title }}</td>
                    <td>{{ $e->start_date?->format('d.m.Y H:i') ?? '' }}</td>
                    <td>{{ $e->end_date?->format('d.m.Y H:i') ?? '' }}</td>
                    <td>{{ $e->location ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
