@props(['title', 'description', 'icon' => 'bx-store-alt'])

<aside class="relative hidden min-h-full overflow-hidden border-l border-slate-100 bg-slate-50/70 p-10 lg:flex lg:flex-col lg:justify-center" aria-hidden="true">
    <div class="absolute -left-16 -top-16 h-48 w-48 rounded-full bg-indigo-100/60"></div>
    <div class="absolute -bottom-16 -right-16 h-56 w-56 rounded-full bg-teal-100/70"></div>
    <div class="relative mx-auto w-full max-w-sm text-center">
        <div class="mx-auto grid h-40 w-40 place-items-center rounded-[2.5rem] border border-indigo-100 bg-white text-indigo-600 shadow-[0_20px_55px_rgba(79,70,229,.14)]">
            <div class="grid h-28 w-28 place-items-center rounded-3xl border-2 border-dashed border-indigo-200 bg-indigo-50"><i class="bx {{ $icon }} text-6xl" aria-hidden="true"></i></div>
        </div>
        <div class="mx-auto mt-8 flex max-w-[250px] items-center justify-center gap-2 rounded-2xl border border-white bg-white/90 p-3 text-right shadow-sm"><i class="bx bx-check-circle text-xl text-teal-500" aria-hidden="true"></i><span class="text-sm font-bold text-slate-700">خطوات واضحة وسهلة</span></div>
        <h2 class="mt-7 text-xl font-extrabold text-slate-800">{{ $title }}</h2>
        <p class="mt-3 leading-7 text-sm text-slate-500">{{ $description }}</p>
    </div>
</aside>
