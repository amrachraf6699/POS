@extends('layouts.app')

@section('title', 'تفاصيل حركة المخزون')

@section('content')
    <div class="space-y-7">
        <div class="border-b border-slate-200 pb-6"><a href="{{ route('inventory.adjustments.index') }}" class="text-sm font-bold text-indigo-600">حركات المخزون</a><h1 class="mt-3 text-3xl font-extrabold text-slate-900">تفاصيل المستند</h1><p class="mt-2 text-sm text-slate-600">هذا مستند مرحّل غير قابل للتعديل أو الحذف.</p></div>
        <section class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:grid-cols-2 sm:p-6">
            <div><p class="text-xs font-bold text-slate-500">النوع</p><p class="mt-1 font-extrabold">{{ match($adjustment->type) { \Modules\Inventory\App\Domain\Enums\StockAdjustmentType::Opening => 'رصيد افتتاحي', \Modules\Inventory\App\Domain\Enums\StockAdjustmentType::AdjustmentIn => 'تسوية زيادة', \Modules\Inventory\App\Domain\Enums\StockAdjustmentType::AdjustmentOut => 'تسوية نقص' } }}</p></div>
            <div><p class="text-xs font-bold text-slate-500">الفرع</p><p class="mt-1 font-extrabold">{{ $adjustment->branch->name }}</p></div>
            <div><p class="text-xs font-bold text-slate-500">المستخدم</p><p class="mt-1 font-extrabold">{{ $adjustment->actor->name }}</p></div>
            <div><p class="text-xs font-bold text-slate-500">تاريخ الترحيل</p><p class="mt-1 font-extrabold">{{ $adjustment->posted_at->format('Y/m/d H:i') }}</p></div>
            <div class="sm:col-span-2"><p class="text-xs font-bold text-slate-500">السبب</p><p class="mt-1 whitespace-pre-line font-semibold text-slate-800">{{ $adjustment->reason }}</p></div>
        </section>
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"><div class="border-b border-slate-100 px-5 py-4 sm:px-6"><h2 class="text-lg font-extrabold">الأصناف المرحّلة</h2></div><div class="overflow-x-auto"><table class="min-w-full text-right text-sm"><thead class="bg-slate-50 text-xs font-bold text-slate-500"><tr><th class="px-5 py-4 sm:px-6">الصنف</th><th class="px-5 py-4">الكمية</th><th class="px-5 py-4 sm:px-6">الرصيد بعد الحركة</th></tr></thead><tbody class="divide-y divide-slate-100">@foreach ($adjustment->items as $item)<tr><th class="px-5 py-4 text-right font-bold sm:px-6">{{ $item->product->name }}</th><td class="px-5 py-4 font-mono">{{ number_format($item->quantity) }}</td><td class="px-5 py-4 font-mono font-bold sm:px-6">{{ number_format($item->movement->balance_after) }}</td></tr>@endforeach</tbody></table></div></section>
    </div>
@endsection
