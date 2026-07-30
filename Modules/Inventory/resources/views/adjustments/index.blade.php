@extends('layouts.app')

@section('title', 'حركات المخزون')

@section('content')
    <div class="space-y-7">
        <div class="flex flex-col gap-4 border-b border-slate-200 pb-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <a href="{{ route('inventory.balances.index') }}" class="text-sm font-bold text-indigo-600">أرصدة المخزون</a>
                <h1 class="mt-3 text-3xl font-extrabold text-slate-900">حركات المخزون</h1>
                <p class="mt-2 text-sm text-slate-600">مستندات الرصيد الافتتاحي والتسويات المرحّلة للقراءة فقط.</p>
            </div>
            @if ($canAdjust)
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('inventory.adjustments.opening.create') }}" class="rounded-xl border border-indigo-200 px-5 py-3 text-sm font-bold text-indigo-700">رصيد افتتاحي</a>
                    <a href="{{ route('inventory.adjustments.create') }}" class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white">تسوية مخزون</a>
                </div>
            @endif
        </div>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="adjustments-title">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6"><h2 id="adjustments-title" class="text-lg font-extrabold">المستندات المرحّلة</h2></div>
            @forelse ($adjustments as $adjustment)
                <a href="{{ route('inventory.adjustments.show', $adjustment) }}" class="flex items-center justify-between gap-4 border-b border-slate-100 px-5 py-5 transition hover:bg-slate-50 sm:px-6 last:border-0">
                    <div>
                        <p class="font-extrabold text-slate-900">{{ match($adjustment->type) { \Modules\Inventory\App\Domain\Enums\StockAdjustmentType::Opening => 'رصيد افتتاحي', \Modules\Inventory\App\Domain\Enums\StockAdjustmentType::AdjustmentIn => 'تسوية زيادة', \Modules\Inventory\App\Domain\Enums\StockAdjustmentType::AdjustmentOut => 'تسوية نقص' } }}</p>
                        <p class="mt-1 text-sm text-slate-600">{{ $adjustment->branch->name }} · {{ $adjustment->items_count }} صنف · {{ $adjustment->posted_at->format('Y/m/d H:i') }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $adjustment->reason }}</p>
                    </div>
                    <i class="bx bx-chevron-left text-2xl text-indigo-600" aria-hidden="true"></i>
                </a>
            @empty
                <div class="px-6 py-16 text-center">
                    <i class="bx bx-clipboard text-4xl text-slate-300" aria-hidden="true"></i>
                    <p class="mt-3 font-semibold text-slate-600">لا توجد مستندات مخزون مرحّلة بعد.</p>
                    @if ($canAdjust)<p class="mt-1 text-sm text-slate-500">ابدأ بإدخال الرصيد الافتتاحي أو تسوية المخزون.</p>@endif
                </div>
            @endforelse
        </section>
    </div>
@endsection
