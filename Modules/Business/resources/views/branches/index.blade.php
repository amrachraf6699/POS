@extends('layouts.app')

@section('title', 'الفروع')

@section('navigation')
    <div class="flex w-full items-center justify-between">
        <a href="{{ route('home') }}" class="font-bold text-slate-900">نظام نقاط البيع</a>
        <a href="{{ route('business.branches.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-bold text-white">إضافة فرع</a>
    </div>
@endsection

@section('content')
    <div class="mb-8 flex items-end justify-between">
        <div><p class="text-sm font-semibold text-indigo-600">إدارة النشاط</p><h1 class="mt-2 text-3xl font-bold">الفروع</h1><p class="mt-2 text-slate-500">أنشئ مواقع العمل وحدد وصول أعضاء الفريق إليها.</p></div>
    </div>
    @if (session('status')) <div class="mb-5 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">{{ session('status') }}</div> @endif
    <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
        @forelse ($branches as $branch)
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-3"><div><h2 class="text-lg font-bold">{{ $branch->name }}</h2><p class="mt-1 font-mono text-sm text-slate-500">{{ $branch->code }}</p></div><span class="rounded-full px-3 py-1 text-xs font-bold {{ $branch->isActive() ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $branch->isActive() ? 'نشط' : 'غير نشط' }}</span></div>
                <p class="mt-5 text-sm text-slate-500">{{ $branch->city ?: 'لم يتم تحديد المدينة' }} · {{ $branch->timezone }}</p>
                <a href="{{ route('business.branches.edit', $branch) }}" class="mt-5 inline-block text-sm font-bold text-indigo-600">إدارة الفرع ←</a>
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-slate-500 md:col-span-2 lg:col-span-3">لا توجد فروع بعد.</div>
        @endforelse
    </div>
@endsection
