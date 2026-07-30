@extends('layouts.app')

@section('title', $fixedType?->value === 'opening' ? 'رصيد افتتاحي' : 'تسوية مخزون')

@section('content')
    @php($isOpening = $fixedType === \Modules\Inventory\App\Domain\Enums\StockAdjustmentType::Opening)
    <div class="space-y-7">
        <div class="border-b border-slate-200 pb-6">
            <a href="{{ route('inventory.adjustments.index') }}" class="text-sm font-bold text-indigo-600">حركات المخزون</a>
            <h1 class="mt-3 text-3xl font-extrabold text-slate-900">{{ $isOpening ? 'إدخال الرصيد الافتتاحي' : 'تسوية المخزون' }}</h1>
            <p class="mt-2 text-sm text-slate-600">{{ $isOpening ? 'يُسمح بإدخال الرصيد الافتتاحي مرة واحدة فقط لكل صنف وفرع.' : 'اختر زيادة أو نقصاً واحداً لكل مستند، مع سبب واضح للتسوية.' }}</p>
        </div>

        <form method="POST" action="{{ $isOpening ? route('inventory.adjustments.opening.store') : route('inventory.adjustments.store') }}" class="space-y-6" novalidate>
            @csrf
            <input type="hidden" name="idempotency_key" value="{{ old('idempotency_key', (string) \Illuminate\Support\Str::uuid()) }}">
            @if ($isOpening)
                <input type="hidden" name="type" value="opening">
            @endif
            @if ($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-700" role="alert">يرجى مراجعة الحقول المطلوبة وقيم الأصناف.</div>
            @endif
            <section class="grid gap-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:grid-cols-2 sm:p-6">
                <label class="block text-sm font-bold text-slate-700">الفرع
                    <select name="branch_id" required class="mt-2 w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">اختر الفرع</option>
                        @foreach ($branches as $branch)<option value="{{ $branch->getKey() }}" @selected((string) old('branch_id') === (string) $branch->getKey())>{{ $branch->name }}</option>@endforeach
                    </select>
                    @error('branch_id')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                </label>
                @unless ($isOpening)
                    <label class="block text-sm font-bold text-slate-700">اتجاه التسوية
                        <select name="type" required class="mt-2 w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="adjustment_in" @selected(old('type', 'adjustment_in') === 'adjustment_in')>زيادة المخزون</option>
                            <option value="adjustment_out" @selected(old('type') === 'adjustment_out')>نقص المخزون</option>
                        </select>
                    </label>
                @endunless
                <label class="block text-sm font-bold text-slate-700 {{ $isOpening ? 'sm:col-span-2' : '' }}">سبب {{ $isOpening ? 'إدخال الرصيد' : 'التسوية' }}
                    <textarea name="reason" required rows="3" maxlength="2000" class="mt-2 w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('reason') }}</textarea>
                    @error('reason')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                </label>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="items-title">
                <div class="flex items-center justify-between gap-4"><div><h2 id="items-title" class="text-lg font-extrabold">الأصناف والكميات</h2><p class="mt-1 text-sm text-slate-500">كل كمية يجب أن تكون رقماً صحيحاً موجباً.</p></div><button type="button" data-add-line class="rounded-xl border border-indigo-200 px-4 py-2 text-sm font-bold text-indigo-700">إضافة صنف</button></div>
                @error('items')<p class="mt-3 text-sm font-semibold text-red-600">{{ $message }}</p>@enderror
                <div data-lines class="mt-5 space-y-3">
                    @foreach (old('items', [['product_id' => '', 'quantity' => '']]) as $index => $item)
                        <div data-line class="grid gap-3 rounded-xl bg-slate-50 p-3 sm:grid-cols-[1fr_150px_auto] sm:items-end">
                            <label class="block text-sm font-bold text-slate-700">الصنف<select name="items[{{ $index }}][product_id]" required class="mt-1 w-full rounded-xl border-slate-300 text-sm"><option value="">اختر الصنف</option>@foreach ($products as $product)<option value="{{ $product->getKey() }}" @selected((string) ($item['product_id'] ?? '') === (string) $product->getKey())>{{ $product->name }}</option>@endforeach</select></label>
                            <label class="block text-sm font-bold text-slate-700">الكمية<input name="items[{{ $index }}][quantity]" type="number" min="1" step="1" required value="{{ $item['quantity'] ?? '' }}" class="mt-1 w-full rounded-xl border-slate-300 text-sm"></label>
                            <button type="button" data-remove-line class="rounded-xl px-3 py-2 text-sm font-bold text-red-600 hover:bg-red-50">حذف</button>
                        </div>
                    @endforeach
                </div>
                @error('items.*.product_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                @error('items.*.quantity')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </section>
            <div class="flex flex-wrap gap-3"><button type="submit" class="rounded-xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white">ترحيل المستند</button><a href="{{ route('inventory.adjustments.index') }}" class="rounded-xl border border-slate-300 px-6 py-3 text-sm font-bold text-slate-700">إلغاء</a></div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    (() => {
        const lines = document.querySelector('[data-lines]');
        const button = document.querySelector('[data-add-line]');
        const options = @json($products->map(fn ($product) => ['id' => $product->getKey(), 'name' => $product->name])->values());
        let next = lines?.querySelectorAll('[data-line]').length || 0;
        button?.addEventListener('click', () => {
            const row = document.createElement('div'); row.dataset.line = '';
            row.className = 'grid gap-3 rounded-xl bg-slate-50 p-3 sm:grid-cols-[1fr_150px_auto] sm:items-end';
            const choices = options.map((product) => `<option value="${product.id}">${product.name}</option>`).join('');
            row.innerHTML = `<label class="block text-sm font-bold text-slate-700">الصنف<select name="items[${next}][product_id]" required class="mt-1 w-full rounded-xl border-slate-300 text-sm"><option value="">اختر الصنف</option>${choices}</select></label><label class="block text-sm font-bold text-slate-700">الكمية<input name="items[${next}][quantity]" type="number" min="1" step="1" required class="mt-1 w-full rounded-xl border-slate-300 text-sm"></label><button type="button" data-remove-line class="rounded-xl px-3 py-2 text-sm font-bold text-red-600 hover:bg-red-50">حذف</button>`;
            lines?.append(row); next += 1;
        });
        lines?.addEventListener('click', (event) => { if (event.target.closest('[data-remove-line]') && lines.querySelectorAll('[data-line]').length > 1) event.target.closest('[data-line]').remove(); });
    })();
</script>
@endpush
