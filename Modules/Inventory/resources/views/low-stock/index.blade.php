@extends('layouts.app')

@section('title', 'الأصناف منخفضة المخزون')

@section('content')
    <div class="space-y-7">
        <div class="flex flex-col gap-4 border-b border-slate-200 pb-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <a href="{{ route('inventory.balances.index') }}" class="text-sm font-bold text-indigo-600">أرصدة المخزون</a>
                <h1 class="mt-3 text-3xl font-extrabold text-slate-900">الأصناف منخفضة المخزون</h1>
                <p class="mt-2 text-sm text-slate-600">الأصناف التي وصل رصيدها في الفرع إلى حد التنبيه أو أقل منه.</p>
            </div>
            <a href="{{ route('inventory.balances.index') }}" class="rounded-xl border border-indigo-200 px-4 py-2 text-sm font-bold text-indigo-700">عرض كل الأرصدة</a>
        </div>

        <form method="GET" action="{{ route('inventory.low-stock.index') }}" class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:grid-cols-2 sm:items-end" aria-label="تصفية الأصناف منخفضة المخزون">
            <label class="block text-sm font-bold text-slate-700">الفرع
                <select name="branch" class="mt-2 w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">كل الفروع المتاحة</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->getKey() }}" @selected((string) ($filters['branch'] ?? '') === (string) $branch->getKey())>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </label>
            <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-200">تطبيق التصفية</button>
        </form>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="low-stock-table-title">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                <h2 id="low-stock-table-title" class="text-lg font-extrabold text-slate-900">الأصناف التي تحتاج متابعة</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $balances->count() }} صنف</p>
            </div>
            @forelse ($balances as $balance)
                @if ($loop->first)
                    <div class="overflow-x-auto"><table class="min-w-full text-right text-sm"><caption class="sr-only">الأصناف منخفضة المخزون حسب الفرع</caption>
                        <thead class="bg-slate-50 text-xs font-bold text-slate-500"><tr><th scope="col" class="px-5 py-4 sm:px-6">الفرع</th><th scope="col" class="px-5 py-4">المنتج</th><th scope="col" class="px-5 py-4">الرصيد الحالي</th><th scope="col" class="px-5 py-4 sm:px-6">حد التنبيه</th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                @endif
                            <tr class="transition hover:bg-slate-50"><th scope="row" class="whitespace-nowrap px-5 py-4 font-bold text-slate-900 sm:px-6">{{ $balance->branch->name }}</th><td class="whitespace-nowrap px-5 py-4 text-slate-700">{{ $balance->product->name }}</td><td class="whitespace-nowrap px-5 py-4 font-mono font-bold text-amber-700">{{ number_format($balance->quantity_on_hand) }}</td><td class="whitespace-nowrap px-5 py-4 font-mono font-bold text-slate-900 sm:px-6">{{ number_format($balance->product->low_stock_threshold) }}</td></tr>
                @if ($loop->last)
                        </tbody></table></div>
                @endif
            @empty
                <div class="px-6 py-16 text-center"><i class="bx bx-check-circle text-4xl text-emerald-300" aria-hidden="true"></i><p class="mt-3 font-semibold text-slate-600">لا توجد أصناف منخفضة المخزون حالياً.</p><p class="mt-1 text-sm text-slate-500">لا تظهر المنتجات التي ليس لها رصيد مسجل في أي فرع.</p></div>
            @endforelse
        </section>
    </div>
@endsection
