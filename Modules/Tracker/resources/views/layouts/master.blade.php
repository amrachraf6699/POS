<!doctype html>
<html lang="en" dir="ltr" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>POS MVP · Agent Tracker</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
                    colors: { ink: '#0f172a', canvas: '#f8fafc', accent: '#6366f1' },
                    boxShadow: { card: '0 1px 2px rgba(15, 23, 42, .05), 0 8px 24px rgba(15, 23, 42, .06)' }
                }
            }
        };
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="min-h-full bg-slate-100 text-slate-900 antialiased">
    @yield('content')
</body>
</html>
