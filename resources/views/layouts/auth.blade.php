<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <script>tailwind.config = { theme: { extend: { fontFamily: { sans: ['Tajawal', 'ui-sans-serif', 'system-ui', 'sans-serif'] }, colors: { brand: '#414bd3' } } } };</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    @stack('head')
</head>
<body class="min-h-screen bg-[#f7f8fc] font-sans text-[#1d2741] antialiased">
    <div class="relative flex min-h-screen items-center justify-center overflow-hidden px-4 py-8 sm:px-8">
        <div class="pointer-events-none absolute inset-0 opacity-60" aria-hidden="true">
            <div class="absolute -bottom-24 -left-16 h-72 w-72 rounded-full bg-[#e9edf9]"></div>
            <div class="absolute -bottom-32 left-24 h-56 w-56 rounded-t-full bg-[#eef1fa]"></div>
            <div class="absolute bottom-0 right-8 h-72 w-44 bg-gradient-to-t from-[#e9edf8] to-transparent [clip-path:polygon(48%_0,55%_0,60%_35%,100%_100%,0_100%,40%_35%)]"></div>
            <div class="absolute -right-32 -top-28 h-80 w-80 rounded-full bg-[#eef0fa]"></div>
        </div>

        <main class="relative z-10 w-full max-w-[610px]">
            <div class="rounded-[22px] border border-[#e1e5ef] bg-white/95 px-6 py-8 shadow-[0_20px_60px_rgba(45,55,90,.10)] sm:px-10 sm:py-11">
                <a href="{{ Route::has('login') ? route('login') : url('/') }}" class="mx-auto flex w-fit items-center gap-3 text-[#18213d]">
                    <span class="grid h-14 w-14 place-items-center rounded-2xl bg-[#4450d5] text-white shadow-lg shadow-indigo-200">
                        <i class="bx bx-store-alt text-3xl" aria-hidden="true"></i>
                    </span>
                    <span class="text-3xl font-extrabold tracking-tight">كاشير <small class="rounded-md bg-[#4450d5] px-1.5 py-0.5 align-middle text-sm font-bold text-white">POS</small></span>
                </a>
                <p class="mt-3 text-center text-sm text-[#8a93a8]">نظام نقاط بيع سحابي مصمم للأعمال في مصر</p>

                @if(session('status'))<div class="mt-6 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">{{ session('status') }}</div>@endif
                @yield('content')
            </div>
            <p class="mt-6 flex items-center justify-center gap-2 text-center text-sm text-[#7b859d]"><i class="bx bx-shield-quarter text-xl text-[#4450d5]" aria-hidden="true"></i>بياناتك آمنة ومحفوظة وفق أعلى معايير الأمان</p>
        </main>
    </div>
    @stack('scripts')
</body>
</html>
