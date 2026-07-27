@extends('layouts.app')

@section('title', 'الفروع')

@section('content')
    <div class="space-y-7">
        <div class="flex flex-col gap-4 border-b border-slate-200 pb-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-bold text-indigo-600">إدارة النشاط</p>
                <h1 class="mt-2 text-3xl font-extrabold text-slate-900">الفروع</h1>
                <p class="mt-2 text-sm text-slate-600">أنشئ مواقع العمل وحدد وصول أعضاء الفريق إليها.</p>
            </div>
            @if($canManage)
                <a href="{{ route('business.branches.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-200">
                    <i class="bx bx-plus text-xl" aria-hidden="true"></i><span>إضافة فرع</span>
                </a>
            @endif
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700" role="status">{{ session('status') }}</div>
        @endif

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="branches-table-title">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 sm:px-6">
                <div><h2 id="branches-table-title" class="text-lg font-extrabold text-slate-900">قائمة الفروع</h2><p class="mt-1 text-sm text-slate-500">{{ $branches->count() }} فرع</p></div>
            </div>
            @if($branches->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="min-w-full text-right text-sm">
                        <caption class="sr-only">الفروع المتاحة ومسارات إدارتها</caption>
                        <thead class="bg-slate-50 text-xs font-bold text-slate-500"><tr><th scope="col" class="px-5 py-4 sm:px-6">اسم الفرع</th><th scope="col" class="px-5 py-4">الرمز</th><th scope="col" class="px-5 py-4">الموقع</th><th scope="col" class="px-5 py-4">الحالة</th><th scope="col" class="px-5 py-4 sm:px-6"><span class="sr-only">إجراء</span></th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($branches as $branch)
                                <tr class="transition hover:bg-slate-50">
                                    <th scope="row" class="whitespace-nowrap px-5 py-4 font-bold text-slate-900 sm:px-6">{{ $branch->name }}</th>
                                    <td class="whitespace-nowrap px-5 py-4 font-mono text-xs font-semibold text-slate-500">{{ $branch->code }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-slate-600">{{ $branch->city ?: 'لم يتم تحديد المدينة' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4"><span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $branch->isActive() ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $branch->isActive() ? 'نشط' : 'غير نشط' }}</span></td>
                                    <td class="whitespace-nowrap px-5 py-4 text-left sm:px-6">@if($canManage)<a href="{{ route('business.branches.edit', $branch) }}" class="font-bold text-indigo-600 underline-offset-4 hover:underline focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">إدارة الفرع</a>@else<span class="text-slate-400">للعرض فقط</span>@endif</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="px-6 py-16 text-center"><i class="bx bx-store text-4xl text-slate-300" aria-hidden="true"></i><p class="mt-3 font-semibold text-slate-600">لا توجد فروع بعد.</p>@if($canManage)<a href="{{ route('business.branches.create') }}" class="mt-4 inline-flex font-bold text-indigo-600 hover:underline">إضافة أول فرع</a>@endif</div>
            @endif
        </section>
    </div>
@endsection
