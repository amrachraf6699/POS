<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ \App\Support\LocaleDirection::for(app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    @stack('head')
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased" dir="{{ \App\Support\LocaleDirection::for(app()->getLocale()) }}">
    <header class="border-b border-slate-200 bg-white">
        <nav class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4" aria-label="{{ __('Navigation') }}">
            @yield('navigation')
        </nav>
    </header>

    <main class="mx-auto max-w-7xl px-6 py-8">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
