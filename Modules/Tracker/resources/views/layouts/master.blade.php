<!doctype html>
<html lang="en" dir="ltr" class="h-full bg-[#09090b]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'POS MVP Tracker')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class', theme: { extend: { fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui'] } } } };</script>
</head>
<body class="min-h-full bg-[#09090b] text-zinc-100 antialiased selection:bg-indigo-500/40">
    @yield('content')
</body>
</html>
