@extends('layouts.app')

@section('title', 'أرصدة المخزون')

@section('content')
    <div class="space-y-7">
        <div class="flex flex-col gap-4 border-b border-slate-200 pb-6 sm:flex-row sm:items-end sm:justify-between">
            <div><a href="{{ route('business.dashboard') }}" class="text-sm font-bold text-indigo-600">لوحة التحكم</a>
                <h1 class="mt-3 text-3xl font-extrabold text-slate-900">أرصدة المخزون</h1>
                <p class="mt-2 text-sm text-slate-600">عرض للقراءة فقط للأرصدة الحالية للمنتجات التي تتبع المخزون.</p></div>
            <a href="{{ route('inventory.transfers.index') }}" class="rounded-xl border border-indigo-200 px-4 py-2 text-sm font-bold text-indigo-700">تحويلات الفروع</a>
            @if ($canAdjust)
                <div class="flex flex-wrap gap-3"><a href="{{ route('inventory.adjustments.opening.create') }}" class="rounded-xl border border-indigo-200 px-4 py-2 text-sm font-bold text-indigo-700">رصيد افتتاحي</a><a href="{{ route('inventory.adjustments.index') }}" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-bold text-white">حركات المخزون</a></div>
            @endif
        </div>

        <form method="GET" action="{{ route('inventory.balances.index') }}"
            class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:grid-cols-3 sm:items-end"
            aria-label="تصفية أرصدة المخزون">
            <label class="block text-sm font-bold text-slate-700">الفرع
                <select name="branch" class="mt-2 w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">كل الفروع</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->getKey() }}" @selected((string) ($filters['branch'] ?? '') === (string) $branch->getKey())>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block text-sm font-bold text-slate-700">المنتج
                <select name="product" class="mt-2 w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">كل المنتجات</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->getKey() }}" @selected((string) ($filters['product'] ?? '') === (string) $product->getKey())>{{ $product->name }}</option>
                    @endforeach
                </select>
            </label>
            <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-200">تطبيق التصفية</button>
        </form>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="balances-table-title">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                <h2 id="balances-table-title" class="text-lg font-extrabold text-slate-900">الأرصدة الحالية</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $balances->count() }} رصيد</p>
            </div>
            @forelse ($balances as $balance)
                @if ($loop->first)
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-right text-sm">
                            <caption class="sr-only">الأرصدة الحالية حسب الفرع والمنتج</caption>
                            <thead class="bg-slate-50 text-xs font-bold text-slate-500">
                                <tr><th scope="col" class="px-5 py-4 sm:px-6">الفرع</th><th scope="col" class="px-5 py-4">المنتج</th><th scope="col" class="px-5 py-4 sm:px-6">الكمية المتاحة</th></tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                @endif
                                <tr class="transition hover:bg-slate-50">
                                    <th scope="row" class="whitespace-nowrap px-5 py-4 font-bold text-slate-900 sm:px-6">{{ $balance->branch->name }}</th>
                                    <td class="whitespace-nowrap px-5 py-4 text-slate-700">{{ $balance->product->name }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 font-mono font-bold text-slate-900 sm:px-6">{{ number_format($balance->quantity_on_hand) }}</td>
                                </tr>
                @if ($loop->last)
                            </tbody>
                        </table>
                    </div>
                @endif
            @empty
                <div class="px-6 py-16 text-center"><i class="bx bx-archive-in text-4xl text-slate-300" aria-hidden="true"></i>
                    <p class="mt-3 font-semibold text-slate-600">لا توجد أرصدة مخزون مسجلة بعد.</p>
                    <p class="mt-1 text-sm text-slate-500">ستظهر الأرصدة هنا بعد تسجيل حركات المخزون المعتمدة.</p>
                </div>
            @endforelse
        </section>
    </div>
@endsection
