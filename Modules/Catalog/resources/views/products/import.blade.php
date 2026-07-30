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
            <h2 class="text-lg font-extrabold">تنزيل نموذج CSV</h2>
            @if ($sampleAvailable)
                <p class="mt-2 text-sm text-slate-600">نزّل نموذجاً جاهزاً يستخدم فئة ونسبة ضريبة نشطتين من نشاطك، ثم عدّل بيانات المنتج قبل الاستيراد.</p>
                <a href="{{ route('catalog.products.import.sample') }}"
                    class="mt-5 inline-flex items-center gap-2 rounded-xl border border-indigo-200 px-5 py-3 text-sm font-bold text-indigo-700 transition hover:bg-indigo-50 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                    <i class="bx bx-download text-xl" aria-hidden="true"></i><span>تنزيل نموذج CSV</span>
                </a>
            @else
                <p class="mt-2 text-sm text-slate-600">أنشئ فئة واحدة على الأقل ونسبة ضريبة نشطة لإتاحة نموذج CSV صالح للاستيراد.</p>
            @endif
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
