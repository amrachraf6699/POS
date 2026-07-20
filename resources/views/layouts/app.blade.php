@php
    $productNavigation = app(\Modules\Business\App\Domain\Navigation\ProductNavigation::class)->build(auth()->user());
    $tenantContext = app(\Modules\Identity\App\Domain\Tenancy\TenantContext::class);
    $currentTenant = $tenantContext->hasTenant() ? $tenantContext->tenant() : null;
    $currentUser = auth()->user();
    $roleLabel = match ($tenantContext->hasTenant() ? $tenantContext->membership()->role : null) {
        'owner' => 'مالك',
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
    <script>tailwind.config = { theme: { extend: { fontFamily: { sans: ['Tajawal', 'ui-sans-serif', 'system-ui', 'sans-serif'] } } } };</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    @stack('head')
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
    <div id="mobile-backdrop" class="fixed inset-0 z-30 hidden bg-slate-950/40 lg:hidden" data-mobile-close></div>
    <aside id="product-sidebar" class="fixed inset-y-0 right-0 z-40 flex w-72 -translate-x-full flex-col border-l border-slate-200 bg-white transition-transform duration-200 lg:translate-x-0" aria-label="التنقل الرئيسي">
        <div class="flex h-20 items-center border-b border-slate-100 px-6"><a href="{{ $currentTenant ? route('business.dashboard') : (Route::has('login') ? route('login') : url('/')) }}" class="text-lg font-bold text-slate-900">نظام نقاط البيع</a></div>
        <nav class="flex-1 space-y-1 overflow-y-auto p-4" aria-label="روابط مساحة العمل">
            @foreach($productNavigation['items'] as $item)
                @php($active = request()->routeIs(...$item['patterns']))
                <a href="{{ $item['url'] }}" @class(['flex items-center rounded-xl px-4 py-3 text-sm font-semibold transition', 'bg-indigo-50 text-indigo-700' => $active, 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' => ! $active])>{{ $item['label'] }}</a>
            @endforeach
            @if($currentTenant)<div class="mt-6 border-t border-slate-100 pt-5"><p class="px-4 text-xs font-bold uppercase tracking-wider text-slate-400">قريباً</p>@foreach($productNavigation['future'] as $future)<span class="mt-1 block rounded-xl px-4 py-2 text-sm text-slate-400">{{ $future }}</span>@endforeach</div>@endif
        </nav>
        @if(auth()->check())<div class="border-t border-slate-100 p-4"><form method="POST" action="{{ route('logout') }}">@csrf<button class="w-full rounded-xl px-4 py-3 text-right text-sm font-semibold text-red-600 hover:bg-red-50">تسجيل الخروج</button></form></div>@endif
    </aside>
    <div class="min-h-screen lg:mr-72">
        <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/95 backdrop-blur"><div class="flex h-20 items-center justify-between px-4 sm:px-6"><button type="button" class="rounded-lg p-2 text-slate-600 hover:bg-slate-100 lg:hidden" aria-controls="product-sidebar" aria-expanded="false" data-mobile-toggle><span class="sr-only">فتح القائمة</span>☰</button><div class="hidden text-sm text-slate-500 sm:block">@yield('breadcrumb')</div><div class="flex items-center gap-3">@if($currentTenant)<button type="button" class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-right shadow-sm transition hover:border-indigo-300" data-tenant-switcher-open aria-haspopup="dialog" aria-controls="tenant-switcher"><span class="hidden text-sm font-semibold text-slate-700 sm:block">{{ $currentTenant->name }}</span><span class="text-indigo-600">⌄</span></button>@endif<span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-bold text-indigo-700">{{ $currentUser?->name ?: 'زائر' }}@if($currentUser) · {{ $roleLabel }}@endif</span></div></div></header>
        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6">@if(session('status'))<div class="mb-6 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">{{ session('status') }}</div>@endif @yield('content')</main>
    </div>
    @if($currentTenant)
        <div id="tenant-switcher" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/40 p-4" role="dialog" aria-modal="true" aria-labelledby="tenant-switcher-title" data-tenant-modal>
            <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl" role="document"><div class="flex items-start justify-between gap-4"><div><h2 id="tenant-switcher-title" class="text-xl font-bold">تبديل مساحة العمل</h2><p class="mt-1 text-sm text-slate-500">اختر النشاط التجاري الذي تريد إدارته.</p></div><button type="button" class="rounded-lg p-2 text-xl text-slate-500 hover:bg-slate-100" aria-label="إغلاق" data-tenant-switcher-close>×</button></div><label class="mt-5 block"><span class="sr-only">البحث عن مساحة عمل</span><input type="search" class="w-full rounded-xl border border-slate-300 px-4 py-3" placeholder="ابحث عن مساحة عمل" data-tenant-search></label><div class="mt-4 max-h-80 space-y-2 overflow-y-auto" data-tenant-list>@foreach($productNavigation['tenants'] as $tenant)<form method="POST" action="{{ route('tenant.selection.store', $tenant) }}" data-tenant-option>@csrf<button type="submit" class="flex w-full items-center justify-between rounded-xl border p-4 text-right transition {{ $currentTenant->is($tenant) ? 'border-indigo-500 bg-indigo-50' : 'border-slate-200 hover:border-indigo-300 hover:bg-indigo-50' }}"><span><span class="block font-bold">{{ $tenant->name }}</span><span class="mt-1 block text-xs text-slate-500">{{ $tenant->slug }}</span></span>@if($currentTenant->is($tenant))<span class="font-bold text-indigo-600">✓</span>@endif</button></form>@endforeach</div><div class="mt-5 flex justify-end"><button type="button" class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold" data-tenant-switcher-close>إلغاء</button></div></div>
        </div>
    @endif
    <script>
        (() => { const sidebar = document.getElementById('product-sidebar'); const backdrop = document.getElementById('mobile-backdrop'); const toggle = document.querySelector('[data-mobile-toggle]'); const tenantModal = document.querySelector('[data-tenant-modal]'); const tenantOpen = document.querySelector('[data-tenant-switcher-open]'); const tenantSearch = document.querySelector('[data-tenant-search]'); const closeMenu = () => { sidebar?.classList.add('-translate-x-full'); backdrop?.classList.add('hidden'); toggle?.setAttribute('aria-expanded', 'false'); }; const openMenu = () => { sidebar?.classList.remove('-translate-x-full'); backdrop?.classList.remove('hidden'); toggle?.setAttribute('aria-expanded', 'true'); }; const closeTenant = () => { tenantModal?.classList.add('hidden'); tenantModal?.classList.remove('flex'); tenantOpen?.focus(); }; const openTenant = () => { tenantModal?.classList.remove('hidden'); tenantModal?.classList.add('flex'); tenantSearch?.focus(); }; toggle?.addEventListener('click', () => sidebar?.classList.contains('-translate-x-full') ? openMenu() : closeMenu()); backdrop?.addEventListener('click', closeMenu); tenantOpen?.addEventListener('click', openTenant); document.querySelectorAll('[data-tenant-switcher-close]').forEach((button) => button.addEventListener('click', closeTenant)); tenantModal?.addEventListener('click', (event) => { if (event.target === tenantModal) closeTenant(); }); tenantSearch?.addEventListener('input', (event) => { const query = event.target.value.toLowerCase(); document.querySelectorAll('[data-tenant-option]').forEach((option) => option.classList.toggle('hidden', !option.textContent.toLowerCase().includes(query))); }); document.addEventListener('keydown', (event) => { if (event.key === 'Escape') { closeMenu(); if (!tenantModal?.classList.contains('hidden')) closeTenant(); } }); })();
    </script>
    @stack('scripts')
</body>
</html>
