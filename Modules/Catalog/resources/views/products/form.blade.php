@extends('layouts.app')
@php($editing = $product->exists)
@section('title', $editing ? 'تعديل المنتج' : 'إضافة منتج')
@section('content')
    <div class="mx-auto max-w-4xl space-y-6">
        <div><a href="{{ route('catalog.products.index') }}" class="text-sm font-bold text-indigo-600">المنتجات</a>
            <h1 class="mt-3 text-3xl font-extrabold">{{ $editing ? 'تعديل المنتج' : 'إضافة منتج جديد' }}</h1>
        </div>
        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">يرجى مراجعة البيانات المدخلة.
            </div>
        @endif
        <form method="POST"
            action="{{ $editing ? route('catalog.products.update', $product) : route('catalog.products.store') }}"
            class="grid gap-5 rounded-2xl border border-slate-200 bg-white p-6 sm:grid-cols-2">
            @csrf @if ($editing)
                @method('PUT')
            @endif
            <div class="sm:col-span-2">
                <label>اسم المنتج</label><input name="name" value="{{ old('name', $product->name) }}" required
                    class="mt-2 h-12 w-full rounded-xl border px-4">
            </div>
            <div><label>الفئة</label><select name="category_id" required class="mt-2 h-12 w-full rounded-xl border px-4">
                    @foreach ($categories as $c)
                        <option value="{{ $c->id }}" @selected(old('category_id', $product->category_id) == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div><label>نسبة الضريبة</label><select name="tax_rate_id" required
                    class="mt-2 h-12 w-full rounded-xl border px-4">
                    @foreach ($taxRates as $t)
                        <option value="{{ $t->id }}" @selected(old('tax_rate_id', $product->tax_rate_id) == $t->id)>{{ $t->name }}
                            ({{ number_format($t->rate_basis_points / 100, 2) }}%)
                        </option>
                    @endforeach
                </select>
            </div>
            <div><label>SKU</label><input name="sku" value="{{ old('sku', $product->sku) }}"
                    class="mt-2 h-12 w-full rounded-xl border px-4" dir="ltr"></div>
            <div><label>الباركود</label><input name="barcode" value="{{ old('barcode', $product->barcode) }}"
                    class="mt-2 h-12 w-full rounded-xl border px-4" dir="ltr"></div>
            <div><label>التكلفة (قرش)</label><input name="cost_price_minor" type="number" min="0"
                    value="{{ old('cost_price_minor', $product->cost_price_minor) }}" required
                    class="mt-2 h-12 w-full rounded-xl border px-4" dir="ltr"></div>
            <div><label>سعر البيع (قرش)</label><input name="selling_price_minor" type="number" min="0"
                    value="{{ old('selling_price_minor', $product->selling_price_minor) }}" required
                    class="mt-2 h-12 w-full rounded-xl border px-4" dir="ltr"></div>
            <div><label>حد المخزون المنخفض</label><input name="low_stock_threshold" type="number" min="0"
                    value="{{ old('low_stock_threshold', $product->low_stock_threshold) }}" required
                    class="mt-2 h-12 w-full rounded-xl border px-4"></div>
            <div class="space-y-3 pt-6"><label><input type="hidden" name="track_inventory" value="0"><input
                        type="checkbox" name="track_inventory" value="1" @checked(old('track_inventory', $product->track_inventory))> تتبع
                    المخزون</label><label><input type="hidden" name="allow_negative_stock" value="0"><input
                        type="checkbox" name="allow_negative_stock" value="1" @checked(old('allow_negative_stock', $product->allow_negative_stock))> السماح
                    بالمخزون السالب</label></div>
            <div class="sm:col-span-2"><label>الوصف</label>
                <textarea name="description" class="mt-2 w-full rounded-xl border px-4 py-3">{{ old('description', $product->description) }}</textarea>
            </div>
            <div class="sm:col-span-2"><button class="rounded-xl bg-indigo-600 px-6 py-3 font-bold text-white">حفظ
                    المنتج</button></div>
        </form>
        @if ($editing && $product->isSaleAvailable())
            <form method="POST" action="{{ route('catalog.products.deactivate', $product) }}">@csrf<button
                    class="rounded-xl border border-red-200 px-5 py-3 text-red-600">تعطيل المنتج</button></form>
            @endif@if ($editing)
                <form method="POST" action="{{ route('catalog.products.destroy', $product) }}">@csrf
                    @method('DELETE')<button class="rounded-xl border border-red-200 px-5 py-3 text-red-600">حذف
                        المنتج</button></form>
            @endif
    </div>
@endsection
