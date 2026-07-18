@extends('layouts.app')

@section('title', 'اختيار مساحة العمل')

@section('navigation')
    <div class="text-lg font-bold text-slate-900">نظام نقاط البيع</div>
@endsection

@section('content')
    <div class="mx-auto max-w-2xl">
        <div class="mb-8">
            <p class="text-sm font-semibold text-indigo-600">مساحات العمل</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">اختر مساحة العمل</h1>
            <p class="mt-2 text-slate-600">اختر النشاط التجاري الذي تريد إدارته الآن.</p>
        </div>

        <div class="space-y-3">
            @forelse ($tenants as $tenant)
                <form method="POST" action="{{ route('tenant.selection.store', $tenant) }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center justify-between rounded-2xl border border-slate-200 bg-white p-5 text-right shadow-sm transition hover:border-indigo-300 hover:shadow-md">
                        <span>
                            <span class="block text-lg font-bold text-slate-900">{{ $tenant->name }}</span>
                            <span class="mt-1 block text-sm text-slate-500">{{ $tenant->slug }}</span>
                        </span>
                        <span class="rounded-full bg-indigo-50 px-3 py-1 text-sm font-semibold text-indigo-700">اختيار</span>
                    </button>
                </form>
            @empty
                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-amber-900">
                    لا توجد مساحة عمل نشطة متاحة لحسابك.
                </div>
            @endforelse
        </div>
    </div>
@endsection
