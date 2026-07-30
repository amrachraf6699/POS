@extends('layouts.app')
@section('title', 'الكتالوج والضرائب')
@section('content')
    <div class="space-y-8">
        <div class="flex flex-col gap-4 border-b border-slate-200 pb-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-bold text-indigo-600">إدارة النشاط</p>
                <h1 class="mt-2 text-3xl font-extrabold text-slate-900">الكتالوج والضرائب</h1>
                <p class="mt-2 text-sm text-slate-600">نظّم فئات المنتجات ونِسَب ضريبة القيمة المضافة.</p>
            </div>
            <div class="flex gap-3"><a href="{{ route('catalog.categories.create') }}"
                    class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white hover:bg-indigo-700">إضافة
                    فئة</a><a href="{{ route('catalog.tax-rates.create') }}"
                    class="rounded-xl border border-indigo-200 px-5 py-3 text-sm font-bold text-indigo-700 hover:bg-indigo-50">إضافة
                    نسبة ضريبة</a></div>
        </div>
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-lg font-extrabold">الفئات</h2>
            </div>
            @forelse($categories as $category)
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 last:border-0">
                    <div>
                        <p class="font-bold">{{ $category->name }}</p>
                        @if ($category->description)
                            <p class="mt-1 text-sm text-slate-500">{{ $category->description }}</p>
                        @endif
                    </div>
                    <a href="{{ route('catalog.categories.edit', $category) }}"
                        class="font-bold text-indigo-600 hover:underline">إدارة</a>
            </div>@empty<div class="px-6 py-12 text-center text-sm font-semibold text-slate-500">لا توجد فئات بعد.</div>
            @endforelse
        </section>
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-lg font-extrabold">نِسَب الضريبة</h2>
                <p class="mt-1 text-sm text-slate-500">كل تغيير مالي يُنشئ إصداراً جديداً.</p>
            </div>
            @forelse($taxRates as $taxRate)
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 last:border-0">
                    <div>
                        <p class="font-bold">{{ $taxRate->name }} <span
                                dir="ltr">{{ number_format($taxRate->rate_basis_points / 100, 2) }}%</span></p>
                        <p class="mt-1 text-sm text-slate-500">من {{ $taxRate->effective_from->format('Y-m-d') }}
                            @if ($taxRate->effective_to)
                                إلى {{ $taxRate->effective_to->format('Y-m-d') }}
                            @endif
                        </p>
                    </div>
                    <div class="flex items-center gap-3"><span
                            class="rounded-full px-3 py-1 text-xs font-bold {{ $taxRate->isActive() ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $taxRate->isActive() ? 'نشطة' : 'معطلة' }}</span><a
                            href="{{ route('catalog.tax-rates.edit', $taxRate) }}"
                            class="font-bold text-indigo-600 hover:underline">إدارة</a></div>
            </div>@empty<div class="px-6 py-12 text-center text-sm font-semibold text-slate-500">لا توجد نِسَب ضريبة
                    بعد.</div>
            @endforelse
        </section>
    </div>
@endsection
