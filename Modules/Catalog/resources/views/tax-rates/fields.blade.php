<div><label for="tax-rate-name" class="text-sm font-bold">التسمية العربية</label><input id="tax-rate-name" name="name"
        value="{{ old('name', $taxRate->name ?? 'ضريبة القيمة المضافة') }}" required
        class="mt-2 h-12 w-full rounded-xl border border-slate-300 px-4"></div>
<div><label for="tax-rate-points" class="text-sm font-bold">النسبة (بنقاط الأساس)</label><input id="tax-rate-points"
        name="rate_basis_points" type="number" min="0" max="10000"
        value="{{ old('rate_basis_points', $taxRate->rate_basis_points ?? 1400) }}" required dir="ltr"
        class="mt-2 h-12 w-full rounded-xl border border-slate-300 px-4">
    <p class="mt-1 text-xs text-slate-500">1400 = 14.00%</p>
</div>
<div><label for="tax-rate-effective-from" class="text-sm font-bold">تاريخ السريان</label><input
        id="tax-rate-effective-from" name="effective_from" type="date"
        value="{{ old('effective_from', isset($taxRate) ? $taxRate->effective_from->format('Y-m-d') : now()->format('Y-m-d')) }}"
        required dir="ltr" class="mt-2 h-12 w-full rounded-xl border border-slate-300 px-4"></div>
