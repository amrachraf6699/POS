@extends('layouts.app')

@section('title', 'إعدادات النشاط التجاري')

@section('navigation')
    <div class="text-lg font-bold text-slate-900">نظام نقاط البيع</div>
@endsection

@section('content')
    <div class="mx-auto max-w-5xl space-y-8">
        <div>
            <p class="text-sm font-semibold text-indigo-600">إعدادات مساحة العمل</p>
            <h1 class="mt-2 text-3xl font-bold text-slate-900">إعدادات النشاط التجاري</h1>
            <p class="mt-2 text-slate-600">اضبط هوية النشاط والضرائب والإيصالات والإعدادات التشغيلية.</p>
        </div>

        @if (session('status'))<div class="rounded-xl bg-emerald-50 p-4 text-emerald-700">{{ session('status') }}</div>@endif
        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-red-700">
                @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('business.settings.update') }}" class="space-y-8">
            @csrf
            @method('PUT')
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-xl font-bold text-slate-900">هوية النشاط</h2>
                <div class="mt-5 grid gap-5 md:grid-cols-2">
                    <label class="text-sm font-medium text-slate-700">اسم العرض<input name="display_name" value="{{ old('display_name', $settings->display_name) }}" required class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3"></label>
                    <label class="text-sm font-medium text-slate-700">الاسم القانوني<input name="legal_name" value="{{ old('legal_name', $settings->legal_name) }}" class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3"></label>
                    <label class="text-sm font-medium text-slate-700 md:col-span-2">العنوان<textarea name="address" class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3">{{ old('address', $settings->address) }}</textarea></label>
                    <label class="text-sm font-medium text-slate-700">الهاتف<input name="phone" value="{{ old('phone', $settings->phone) }}" class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3"></label>
                    <label class="text-sm font-medium text-slate-700">البريد الإلكتروني<input name="email" type="email" value="{{ old('email', $settings->email) }}" class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3"></label>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-xl font-bold text-slate-900">العملة والضريبة</h2>
                <div class="mt-5 grid gap-5 md:grid-cols-3">
                    <label class="text-sm font-medium text-slate-700">المنطقة الزمنية<input name="timezone" value="{{ old('timezone', $settings->timezone) }}" required class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3"></label>
                    <label class="text-sm font-medium text-slate-700">العملة<select name="currency_code" class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3">@foreach ($currencies as $currency)<option value="{{ $currency }}" @selected(old('currency_code', $settings->currency_code) === $currency)>{{ $currency }}</option>@endforeach</select></label>
                    <label class="text-sm font-medium text-slate-700">نسبة ضريبة القيمة المضافة<input name="vat_rate" type="number" min="0" max="100" step="0.01" value="{{ old('vat_rate', $settings->vat_rate) }}" required class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3"></label>
                </div>
                <input type="hidden" name="vat_enabled" value="1"><input type="hidden" name="vat_mode" value="inclusive">
                <p class="mt-4 rounded-xl bg-slate-50 p-4 text-sm text-slate-600">الأسعار شاملة الضريبة في هذا الإصدار.</p>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-xl font-bold text-slate-900">الإيصالات والتشغيل</h2>
                <div class="mt-5 grid gap-5 md:grid-cols-2">
                    <label class="text-sm font-medium text-slate-700">بادئة رقم الإيصال<input name="receipt_prefix" value="{{ old('receipt_prefix', $settings->receipt_prefix) }}" required class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3"></label>
                    <label class="text-sm font-medium text-slate-700">حد التنبيه للمخزون<input name="low_stock_threshold" type="number" min="0" value="{{ old('low_stock_threshold', $settings->low_stock_threshold) }}" required class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3"></label>
                    <label class="text-sm font-medium text-slate-700">رأس الإيصال<textarea name="receipt_header" class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3">{{ old('receipt_header', $settings->receipt_header) }}</textarea></label>
                    <label class="text-sm font-medium text-slate-700">تذييل الإيصال<textarea name="receipt_footer" class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3">{{ old('receipt_footer', $settings->receipt_footer) }}</textarea></label>
                </div>
                <div class="mt-5 grid gap-3 text-sm text-slate-700 md:grid-cols-3">
                    <label><input type="hidden" name="receipt_show_cashier" value="0"><input type="checkbox" name="receipt_show_cashier" value="1" @checked(old('receipt_show_cashier', $settings->receipt_show_cashier))> إظهار الكاشير</label>
                    <label><input type="hidden" name="receipt_show_date" value="0"><input type="checkbox" name="receipt_show_date" value="1" @checked(old('receipt_show_date', $settings->receipt_show_date))> إظهار التاريخ</label>
                    <label><input type="hidden" name="receipt_show_tax_breakdown" value="0"><input type="checkbox" name="receipt_show_tax_breakdown" value="1" @checked(old('receipt_show_tax_breakdown', $settings->receipt_show_tax_breakdown))> إظهار تفاصيل الضريبة</label>
                    <label><input type="hidden" name="allow_negative_stock" value="0"><input type="checkbox" name="allow_negative_stock" value="1" @checked(old('allow_negative_stock', $settings->allow_negative_stock))> السماح بالمخزون السالب</label>
                </div>
            </section>

            <button class="rounded-xl bg-indigo-600 px-6 py-3 font-semibold text-white hover:bg-indigo-700">حفظ الإعدادات</button>
        </form>
    </div>
@endsection
