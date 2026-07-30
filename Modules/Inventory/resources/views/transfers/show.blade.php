@extends('layouts.app')

@section('title', 'تفاصيل تحويل المخزون')

@section('content')
    <div class="space-y-7">
        <div class="border-b border-slate-200 pb-6">
            <a href="{{ route('inventory.transfers.index') }}" class="text-sm font-bold text-indigo-600">تحويلات الفروع</a>
            <h1 class="mt-3 text-3xl font-extrabold">تفاصيل التحويل</h1>
        </div>

        @if (session('status'))
            <div class="rounded-xl bg-emerald-50 p-4 text-emerald-800">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl bg-red-50 p-4 text-red-800">{{ $errors->first() }}</div>
        @endif

        <section class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-6 sm:grid-cols-2">
            <div><p class="text-sm text-slate-500">من الفرع</p><p class="font-bold">{{ $transfer->sourceBranch->name }}</p></div>
            <div><p class="text-sm text-slate-500">إلى الفرع</p><p class="font-bold">{{ $transfer->destinationBranch->name }}</p></div>
            <div><p class="text-sm text-slate-500">المنشئ</p><p class="font-bold">{{ $transfer->creator->name }}</p></div>
            <div><p class="text-sm text-slate-500">الحالة</p><p class="font-bold">{{ $transfer->status->value }}</p></div>
            <div class="sm:col-span-2"><p class="text-sm text-slate-500">السبب</p><p class="whitespace-pre-line font-bold">{{ $transfer->reason }}</p></div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
            <table class="min-w-full text-right">
                <thead class="bg-slate-50"><tr><th class="p-4">الصنف</th><th class="p-4">الكمية</th></tr></thead>
                <tbody>
                    @foreach ($transfer->items as $item)
                        <tr class="border-t"><td class="p-4">{{ $item->product->name }}</td><td class="p-4">{{ number_format($item->quantity) }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        @if ($canApprove)
            <form method="POST" action="{{ route('inventory.transfers.approve', $transfer) }}">
                @csrf
                <button class="rounded-xl bg-indigo-600 px-5 py-3 font-bold text-white">اعتماد وترحيل التحويل</button>
            </form>
        @endif

        @if ($canCancel)
            <form method="POST" action="{{ route('inventory.transfers.cancel', $transfer) }}" class="flex gap-3">
                @csrf
                <label class="sr-only" for="cancellation-reason">سبب الإلغاء</label>
                <input id="cancellation-reason" name="reason" required placeholder="سبب الإلغاء" class="rounded-xl border-slate-300">
                <button class="rounded-xl border border-red-300 px-5 py-3 font-bold text-red-700">إلغاء التحويل</button>
            </form>
        @endif
    </div>
@endsection
