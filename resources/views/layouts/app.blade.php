@php
    $productNavigation = app(\Modules\Business\App\Domain\Navigation\ProductNavigation::class)->build(auth()->user());
    $tenantContext = app(\Modules\Identity\App\Domain\Tenancy\TenantContext::class);
    $currentTenant = $tenantContext->hasTenant() ? $tenantContext->tenant() : null;
    $currentUser = auth()->user();
    $roleLabel = match ($tenantContext->hasTenant() ? $tenantContext->membership()->role : null) {
        'owner' => 'مالك النظام',
        'manager' => 'مدير',
        default => 'عضو',
    };
@endphp
<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <script>tailwind.config = { theme: { extend: { fontFamily: { sans: ['Tajawal', 'ui-sans-serif', 'system-ui', 'sans-serif'] } } } };</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style>
        #product-sidebar { transform: translateX(100%); visibility: hidden; }
        #product-sidebar.is-open { transform: translateX(0); visibility: visible; }
        @media (min-width: 1024px) {
            #product-sidebar { transform: translateX(0); visibility: visible; }
        }
    </style>
    @stack('head')
</head>
<body class="min-h-screen bg-[#f4f6fb] p-0 font-sans text-[#1d2741] antialiased sm:p-4">
    <div class="relative mx-auto min-h-[calc(100vh-2rem)] max-w-[1540px] overflow-visible rounded-none border border-[#e3e7ef] bg-white shadow-[0_8px_30px_rgba(38,52,86,.06)] sm:rounded-2xl lg:overflow-hidden">
        <div id="mobile-backdrop" class="fixed inset-0 z-30 hidden bg-[#1d2741]/40 lg:hidden" data-mobile-close></div>
        <aside id="product-sidebar" class="fixed inset-y-0 right-0 z-40 flex w-[min(86vw,320px)] translate-x-full flex-col border-l border-[#e6e9f0] bg-white shadow-2xl transition-transform duration-200 lg:absolute lg:inset-y-4 lg:right-4 lg:w-[278px] lg:translate-x-0 lg:rounded-r-2xl lg:shadow-none" aria-label="التنقل الرئيسي" aria-hidden="true">
            <div class="flex h-[88px] items-center justify-between gap-3 border-b border-[#edf0f5] px-5 sm:px-7"><div class="flex items-center gap-3"><span class="grid h-12 w-12 place-items-center rounded-xl bg-[#3446c7] text-white"><i class="bx bx-store-alt text-3xl" aria-hidden="true"></i></span><div><p class="text-xl font-extrabold text-[#25366f]">كاشير مصر</p><p class="text-xs text-[#6f7b96]">نقطة بيع سحابية</p></div></div><button type="button" class="grid h-10 w-10 place-items-center rounded-lg text-2xl text-[#52617d] hover:bg-[#f2f4fa] lg:hidden" aria-label="إغلاق القائمة" data-mobile-close><i class="bx bx-x" aria-hidden="true"></i></button></div>
            <nav class="flex-1 space-y-2 overflow-y-auto px-4 py-6" aria-label="روابط مساحة العمل">
                @foreach($productNavigation['items'] as $item)
                    @php($active = request()->routeIs(...$item['patterns']))
                    <a href="{{ $item['url'] }}" @class(['flex items-center gap-4 rounded-xl px-5 py-3.5 text-[15px] font-bold transition', 'bg-[#eef0ff] text-[#3548c9]' => $active, 'text-[#263149] hover:bg-[#f6f7fc]' => ! $active])>
                        <i class="bx {{ $item['icon'] }} text-xl opacity-85" aria-hidden="true"></i><span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
                @if($currentTenant)
                    <div class="mt-8 border-t border-[#edf0f5] pt-6"><p class="px-5 text-xs font-bold text-[#a0a8ba]">قريباً</p>@foreach($productNavigation['future'] as $future)<span class="mt-3 flex items-center gap-4 px-5 text-[15px] font-semibold text-[#a9b0bf]"><i class="bx {{ $future['icon'] }} text-lg" aria-hidden="true"></i>{{ $future['label'] }}</span>@endforeach</div>
                @endif
            </nav>
            @if(auth()->check())<div class="border-t border-[#edf0f5] p-5"><form method="POST" action="{{ route('logout') }}">@csrf<button class="w-full rounded-xl px-4 py-3 text-right text-sm font-bold text-red-500 hover:bg-red-50">تسجيل الخروج</button></form></div>@endif
        </aside>

        <div class="min-h-[calc(100vh-2rem)] lg:mr-[278px]">
            <header class="border-b border-[#e8ebf1] bg-white"><div class="flex h-[88px] items-center justify-between gap-4 px-5 sm:px-8">
                <div class="flex items-center gap-3"><button type="button" class="grid h-10 w-10 place-items-center rounded-lg text-2xl text-[#33415e] hover:bg-[#f2f4fa] lg:hidden" aria-controls="product-sidebar" aria-expanded="false" data-mobile-toggle><span class="sr-only">فتح القائمة</span><i class="bx bx-menu" aria-hidden="true"></i></button><div class="hidden h-9 w-px bg-[#edf0f5] sm:block"></div><div class="flex items-center gap-3"><span class="grid h-10 w-10 place-items-center rounded-full bg-[#edf0f7] text-[#66738b]"><i class="bx bx-user text-xl" aria-hidden="true"></i></span><div class="hidden sm:block"><p class="text-sm font-bold text-[#253149]">{{ $currentUser?->name ?: 'زائر' }}</p><p class="mt-0.5 text-xs text-[#7c879c]">{{ $roleLabel }} <span class="text-emerald-500">●</span></p></div><i class="bx bx-chevron-down text-xl text-[#52617d]" aria-hidden="true"></i></div></div>
                <div class="flex items-center gap-3"><button type="button" class="flex h-14 min-w-[190px] items-center justify-between gap-3 rounded-xl border border-[#dfe4ee] bg-white px-4 text-right shadow-sm transition hover:border-[#5966da]" data-tenant-switcher-open aria-haspopup="dialog" aria-controls="tenant-switcher"><i class="bx bx-buildings text-xl text-[#3449c8]" aria-hidden="true"></i><span class="flex-1"><span class="block text-base font-bold text-[#27324a]">{{ $currentTenant?->name ?: 'اختر مساحة العمل' }}</span><span class="block text-xs text-[#8a94aa]">المستأجر الحالي</span></span><i class="bx bx-chevron-down text-xl text-[#52617d]" aria-hidden="true"></i></button></div>
            </div></header>
            <main class="mx-auto max-w-[1250px] px-5 py-8 sm:px-8">@if(session('status'))<div class="mb-6 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">{{ session('status') }}</div>@endif @yield('content')</main>
        </div>
    </div>

    @if($currentTenant)
        <div id="tenant-switcher" class="fixed inset-0 z-50 hidden items-center justify-center bg-[#73809d]/55 p-4 backdrop-blur-[2px]" role="dialog" aria-modal="true" aria-labelledby="tenant-switcher-title" data-tenant-modal>
<div class="w-full max-w-[560px] rounded-2xl bg-white p-6 shadow-[0_24px_70px_rgba(34,45,83,.25)] sm:p-7" role="document"><div class="flex items-start justify-between gap-4"><div><h2 id="tenant-switcher-title" class="text-3xl font-extrabold text-[#202b45]">تبديل مساحة العمل</h2><p class="mt-2 text-base text-[#8993a8]">اختر مساحة العمل التي تريد الانتقال إليها.</p></div><button type="button" class="grid h-10 w-10 place-items-center rounded-lg border border-[#dfe4ee] text-2xl text-[#52617d] hover:bg-[#f4f6fb]" aria-label="إغلاق" data-tenant-switcher-close><i class="bx bx-x" aria-hidden="true"></i></button></div><label class="relative mt-7 block"><span class="sr-only">البحث عن مساحة عمل</span><input type="search" class="h-14 w-full rounded-xl border border-[#dfe4ee] px-5 pl-14 text-right text-base outline-none focus:border-[#5262dc] focus:ring-4 focus:ring-indigo-100" placeholder="ابحث عن مساحة عمل" data-tenant-search><i class="bx bx-search pointer-events-none absolute left-4 top-4 text-2xl text-[#69758d]" aria-hidden="true"></i></label><p class="mt-5 text-base font-medium text-[#77839a]">مساحات العمل المتاحة</p><div class="mt-3 max-h-[430px] space-y-3 overflow-y-auto" data-tenant-list>@foreach($productNavigation['tenants'] as $tenant)<form method="POST" action="{{ route('tenant.selection.store', $tenant) }}" data-tenant-option>@csrf<button type="submit" class="flex w-full items-center gap-4 rounded-xl border p-4 text-right transition {{ $currentTenant->is($tenant) ? 'border-[#5461dd] bg-[#f0f1ff]' : 'border-[#e0e4ed] hover:border-[#9da7e7] hover:bg-[#fafaff]' }}"><span class="grid h-12 w-12 shrink-0 place-items-center rounded-xl {{ $currentTenant->is($tenant) ? 'bg-[#7982ea] text-white' : 'bg-[#f0f2f7] text-[#6d7890]' }}"><i class="bx bx-buildings" aria-hidden="true"></i></span><span class="flex-1"><span class="block text-lg font-bold text-[#253149]">{{ $tenant->name }}</span><span class="mt-1 block text-sm text-[#8993a8]">{{ $tenant->slug }}</span></span>@if($currentTenant->is($tenant))<span class="grid h-7 w-7 place-items-center rounded-full bg-[#5361d8] text-white"><i class="bx bx-check" aria-hidden="true"></i></span>@endif</button></form>@endforeach</div><div class="mt-7 flex justify-start gap-3"><button type="button" class="h-14 rounded-xl border border-[#dfe4ee] px-8 text-base font-bold text-[#65718b]" data-tenant-switcher-close>إلغاء</button><span class="hidden h-14 items-center rounded-xl bg-[#4450d5] px-8 text-base font-bold text-white">تبديل</span></div></div>
        </div>
    @endif
    <script>
        (() => { const sidebar = document.getElementById('product-sidebar'); const backdrop = document.getElementById('mobile-backdrop'); const toggle = document.querySelector('[data-mobile-toggle]'); const closeButton = document.querySelector('[data-mobile-close]'); const tenantModal = document.querySelector('[data-tenant-modal]'); const tenantOpen = document.querySelector('[data-tenant-switcher-open]'); const tenantSearch = document.querySelector('[data-tenant-search]'); let lastFocused = null; const closeMenu = ({restoreFocus = true} = {}) => { sidebar?.classList.remove('is-open'); sidebar?.setAttribute('aria-hidden', 'true'); backdrop?.classList.add('hidden'); toggle?.setAttribute('aria-expanded', 'false'); document.body.classList.remove('overflow-hidden'); if (restoreFocus) (lastFocused || toggle)?.focus(); }; const openMenu = () => { lastFocused = document.activeElement; sidebar?.classList.add('is-open'); sidebar?.setAttribute('aria-hidden', 'false'); backdrop?.classList.remove('hidden'); toggle?.setAttribute('aria-expanded', 'true'); document.body.classList.add('overflow-hidden'); closeButton?.focus(); }; const closeTenant = () => { tenantModal?.classList.add('hidden'); tenantModal?.classList.remove('flex'); tenantOpen?.focus(); }; const openTenant = () => { tenantModal?.classList.remove('hidden'); tenantModal?.classList.add('flex'); tenantSearch?.focus(); }; toggle?.addEventListener('click', () => sidebar?.classList.contains('is-open') ? closeMenu() : openMenu()); closeButton?.addEventListener('click', () => closeMenu()); backdrop?.addEventListener('click', () => closeMenu()); sidebar?.querySelectorAll('a, button').forEach((element) => element.addEventListener('click', () => { if (window.matchMedia('(max-width: 1023px)').matches) closeMenu({restoreFocus: false}); })); tenantOpen?.addEventListener('click', openTenant); document.querySelectorAll('[data-tenant-switcher-close]').forEach((button) => button.addEventListener('click', closeTenant)); tenantModal?.addEventListener('click', (event) => { if (event.target === tenantModal) closeTenant(); }); tenantSearch?.addEventListener('input', (event) => { const query = event.target.value.toLowerCase(); document.querySelectorAll('[data-tenant-option]').forEach((option) => option.classList.toggle('hidden', !option.textContent.toLowerCase().includes(query))); }); window.addEventListener('resize', () => { if (window.matchMedia('(min-width: 1024px)').matches) closeMenu({restoreFocus: false}); }); document.addEventListener('keydown', (event) => { if (event.key === 'Escape') { closeMenu(); if (!tenantModal?.classList.contains('hidden')) closeTenant(); } }); closeMenu({restoreFocus: false}); })();
    </script>
    @stack('scripts')
</body>
</html>
