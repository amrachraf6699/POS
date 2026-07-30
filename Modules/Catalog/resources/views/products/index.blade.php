@extends('layouts.app')
@section('title', 'المنتجات')
@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 border-b border-slate-200 pb-6 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="{{ route('catalog.index') }}" class="text-sm font-bold text-indigo-600">الكتالوج والضرائب</a>
            <h1 class="mt-3 text-3xl font-extrabold">المنتجات</h1>
        </div>
        <div class="flex flex-wrap gap-3">
            @if($canImport)<a href="{{ route('catalog.products.import.form') }}" class="rounded-xl border border-indigo-200 px-5 py-3 text-sm font-bold text-indigo-700">استيراد CSV</a>@endif
            @if($canExport)<a href="{{ route('catalog.products.export') }}" class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-bold text-slate-700">تصدير CSV</a>@endif
            @if($canCreate)<a href="{{ route('catalog.products.create') }}" class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white">إضافة منتج</a>@endif
        </div>
    </div>
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
        @forelse($products as $product)
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 last:border-0">
                <div><p class="font-bold">{{ $product->name }}</p><p class="mt-1 text-sm text-slate-500">{{ $product->category->name }} · {{ number_format($product->selling_price_minor / 100, 2) }} ج.م</p></div>
                <div class="flex gap-3"><span class="text-xs font-bold {{ $product->isSaleAvailable() ? 'text-emerald-600' : 'text-slate-400' }}">{{ $product->isSaleAvailable() ? 'نشط' : 'غير نشط' }}</span>@if($canUpdate)<a href="{{ route('catalog.products.edit', $product) }}" class="font-bold text-indigo-600">إدارة</a>@endif</div>
            </div>
        @empty
            <div class="px-6 py-12 text-center text-sm text-slate-500">لا توجد منتجات بعد.</div>
        @endforelse
    </section>
</div>
@endsection
