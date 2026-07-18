<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>متابعة مشروع POS</title>
    <style>
        :root { color-scheme: light; font-family: Tahoma, Arial, sans-serif; }
        body { margin: 0; background: #f4f7fb; color: #172033; }
        .container { width: min(1180px, calc(100% - 32px)); margin: 0 auto; }
        header { background: #172033; color: #fff; padding: 32px 0; }
        h1, h2, h3, p { margin-top: 0; }
        main { padding: 28px 0 48px; }
        .summary, .phase-grid { display: grid; gap: 16px; }
        .summary { grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); margin-bottom: 24px; }
        .phase-grid { grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); }
        .card { background: #fff; border: 1px solid #e3e9f2; border-radius: 14px; padding: 18px; box-shadow: 0 4px 14px rgba(23, 32, 51, .05); }
        .metric { font-size: 1.8rem; font-weight: 700; margin-bottom: 4px; }
        .muted { color: #68758a; font-size: .9rem; }
        .bar { height: 10px; background: #e8edf4; border-radius: 999px; overflow: hidden; margin: 12px 0; }
        .bar > span { display: block; height: 100%; background: #2878d0; border-radius: inherit; }
        .status { display: inline-block; border-radius: 999px; padding: 4px 9px; font-size: .78rem; font-weight: 700; }
        .status-done { background: #d9f4e2; color: #16703b; }
        .status-in_progress, .status-review { background: #fff0c9; color: #805c00; }
        .status-blocked { background: #ffe0e0; color: #a32222; }
        .status-not_started, .status-planned { background: #e8edf4; color: #536174; }
        details { border-top: 1px solid #edf0f5; padding-top: 12px; margin-top: 14px; }
        summary { cursor: pointer; font-weight: 700; }
        .task { padding: 12px 0; border-bottom: 1px solid #edf0f5; }
        .task:last-child { border-bottom: 0; }
        .task-title { display: flex; justify-content: space-between; gap: 12px; align-items: center; }
        .issue { margin-top: 8px; color: #9a2b2b; font-size: .88rem; }
        ul { margin: 8px 0 0; padding-right: 20px; }
        @media (max-width: 600px) { .container { width: min(100% - 20px, 1180px); } .phase-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    @yield('content')
</body>
</html>
