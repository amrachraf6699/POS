@extends('layouts.app')

@section('title', 'تحويل مخزون جديد')

@section('content')
    <div class="mx-auto max-w-4xl space-y-7">
        <div class="border-b border-slate-200 pb-6">
            <a href="{{ route('inventory.transfers.index') }}" class="text-sm font-bold text-indigo-600">تحويلات الفروع</a>
            <h1 class="mt-3 text-3xl font-extrabold">تحويل مخزون جديد</h1>
        </div>
        @if ($errors->any())<div class="rounded-xl bg-red-50 p-4 text-red-800" role="alert">{{ $errors->first() }}</div>@endif
        <form method="POST" action="{{ route('inventory.transfers.store') }}" class="space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            <input type="hidden" name="idempotency_key" value="{{ old('idempotency_key', (string) \Illuminate\Support\Str::uuid()) }}">
            <div class="grid gap-5 sm:grid-cols-2">
                <label class="font-bold">من فرع<select name="source_branch_id" class="mt-2 w-full rounded-xl border-slate-300">@foreach ($branches as $branch)<option value="{{ $branch->getKey() }}">{{ $branch->name }}</option>@endforeach</select></label>
                <label class="font-bold">إلى فرع<select name="destination_branch_id" class="mt-2 w-full rounded-xl border-slate-300">@foreach ($branches as $branch)<option value="{{ $branch->getKey() }}">{{ $branch->name }}</option>@endforeach</select></label>
            </div>
            <label class="block font-bold">السبب<textarea name="reason" class="mt-2 w-full rounded-xl border-slate-300" required>{{ old('reason') }}</textarea></label>
            <div id="transfer-lines" class="space-y-3" aria-live="polite">
                @foreach (old('items', [['product_id' => null, 'quantity' => 1]]) as $index => $item)
                    <div class="transfer-line grid gap-3 sm:grid-cols-[1fr_9rem_auto]">
                        <select name="items[{{ $index }}][product_id]" class="rounded-xl border-slate-300" required>@foreach ($products as $product)<option value="{{ $product->getKey() }}" @selected((string) ($item['product_id'] ?? '') === (string) $product->getKey())>{{ $product->name }}</option>@endforeach</select>
                        <input name="items[{{ $index }}][quantity]" type="number" min="1" value="{{ $item['quantity'] ?? 1 }}" class="rounded-xl border-slate-300" required>
                        <button type="button" class="remove-line rounded-xl border border-slate-300 px-3 text-sm font-bold">إزالة</button>
                    </div>
                @endforeach
            </div>
            <button id="add-transfer-line" type="button" class="rounded-xl border border-indigo-200 px-4 py-2 text-sm font-bold text-indigo-700">إضافة صنف</button>
            <button class="mr-2 rounded-xl bg-indigo-600 px-5 py-3 font-bold text-white">تسجيل التحويل</button>
        </form>
    </div>
    <template id="transfer-line-template"><div class="transfer-line grid gap-3 sm:grid-cols-[1fr_9rem_auto]"><select class="rounded-xl border-slate-300" required>@foreach ($products as $product)<option value="{{ $product->getKey() }}">{{ $product->name }}</option>@endforeach</select><input type="number" min="1" value="1" class="rounded-xl border-slate-300" required><button type="button" class="remove-line rounded-xl border border-slate-300 px-3 text-sm font-bold">إزالة</button></div></template>
    <script>
        (() => { const lines = document.getElementById('transfer-lines'); const renumber = () => lines.querySelectorAll('.transfer-line').forEach((line, index) => { line.querySelector('select').name = `items[${index}][product_id]`; line.querySelector('input').name = `items[${index}][quantity]`; }); document.getElementById('add-transfer-line').addEventListener('click', () => { lines.append(document.getElementById('transfer-line-template').content.cloneNode(true)); renumber(); }); lines.addEventListener('click', (event) => { if (event.target.classList.contains('remove-line') && lines.children.length > 1) { event.target.closest('.transfer-line').remove(); renumber(); } }); })();
    </script>
@endsection
