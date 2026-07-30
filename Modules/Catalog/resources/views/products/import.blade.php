@extends('layouts.app')
@section('title', 'استيراد المنتجات')
@section('content')
    @php($result = session('catalog_import_result'))
    <div class="mx-auto max-w-4xl space-y-6">
        <div>
            <a href="{{ route('catalog.products.index') }}" class="text-sm font-bold text-indigo-600">العودة إلى المنتجات</a>
            <h1 class="mt-3 text-3xl font-extrabold">استيراد المنتجات من CSV</h1>
            <p class="mt-2 text-sm text-slate-500">الاستيراد ينشئ منتجات جديدة فقط؛ لا يحدّث المنتجات الحالية.</p>
        </div>
        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-700">يرجى اختيار ملف
                CSV صالح لا يتجاوز 1 ميغابايت.</div>
        @endif
        <section class="rounded-2xl border border-slate-200 bg-white p-6">
            <h2 class="text-lg font-extrabold">تنسيق الملف المعتمد</h2>
            <p class="mt-2 text-sm text-slate-600">UTF-8، وبحد أقصى 500 صف. يجب أن يكون الصف الأول مطابقاً تماماً لهذا
                الترتيب:</p>
            <code dir="ltr"
                class="mt-4 block overflow-x-auto rounded-lg bg-slate-900 p-4 text-xs text-slate-100">name,category_name,tax_rate_name,sku,barcode,description,cost_price_minor,selling_price_minor,track_inventory,low_stock_threshold,allow_negative_stock,status</code>
            <p class="mt-3 text-sm text-slate-600">استخدم أسماء الفئات ونِسَب الضريبة الموجودة في نشاطك. الأسعار والحدود
                بالقرش، العلامات 0 أو 1، والحالة <span dir="ltr">active</span> أو <span dir="ltr">inactive</span>.
            </p>
        </section>
        <form method="POST" action="{{ route('catalog.products.import.store') }}" enctype="multipart/form-data"
            class="rounded-2xl border border-slate-200 bg-white p-6">
            @csrf
            <label for="catalog_csv" class="block text-sm font-bold">ملف المنتجات CSV</label>
            <input id="catalog_csv" name="catalog_csv" type="file" accept=".csv,text/csv" required
                class="mt-3 block w-full rounded-xl border border-slate-300 p-3" dir="ltr">
            <button class="mt-5 rounded-xl bg-indigo-600 px-6 py-3 font-bold text-white">بدء الاستيراد</button>
        </form>
        @if (is_array($result))
            <section class="rounded-2xl border border-slate-200 bg-white p-6">
                <h2 class="text-lg font-extrabold">نتيجة الاستيراد</h2>
                <p class="mt-2 text-sm text-slate-600">تم استيراد {{ $result['imported_rows'] }} من
                    {{ $result['total_rows'] }} صف.</p>
                @if ($result['errors'] !== [])
                    <div class="mt-5 overflow-x-auto">
                        <table class="min-w-full text-right text-sm">
                            <thead class="border-b text-slate-500">
                                <tr>
                                    <th class="px-3 py-2">الصف</th>
                                    <th class="px-3 py-2">الحقل</th>
                                    <th class="px-3 py-2">المشكلة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($result['errors'] as $error)
                                    <tr class="border-b border-slate-100">
                                        <td class="px-3 py-3" dir="ltr">{{ $error['row'] }}</td>
                                        <td class="px-3 py-3" dir="ltr">{{ $error['field'] }}</td>
                                        <td class="px-3 py-3 text-red-700">{{ $error['message'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        @endif
    </div>
@endsection
