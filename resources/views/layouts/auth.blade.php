<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { fontFamily: { sans: ['Tajawal', 'ui-sans-serif', 'system-ui', 'sans-serif'] } } } };</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    @stack('head')
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-indigo-50 font-sans text-slate-900 antialiased">
    <header class="mx-auto flex max-w-6xl items-center justify-between px-5 py-6 sm:px-8">
        <a href="{{ route('login') }}" class="flex items-center gap-3 text-lg font-bold"><span class="grid h-10 w-10 place-items-center rounded-xl bg-indigo-600 text-xl text-white shadow-lg shadow-indigo-200">م</span><span>نظام نقاط البيع</span></a>
        <div class="text-sm font-semibold text-slate-600">@yield('navigation')</div>
    </header>
    <main class="mx-auto flex min-h-[calc(100vh-104px)] max-w-6xl items-start justify-center px-5 pb-12 pt-6 sm:px-8 sm:pt-10">
        <div class="w-full max-w-xl">
            @if(session('status'))<div class="mb-5 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">{{ session('status') }}</div>@endif
            @yield('content')
        </div>
    </main>
    @stack('scripts')
</body>
</html>
