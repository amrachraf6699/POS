@extends('layouts.app')

@section('title', 'إعدادات النشاط التجاري')

@section('content')
    <div class="mx-auto max-w-5xl space-y-7">
        <div><p class="text-sm font-bold text-indigo-600">إعدادات مساحة العمل</p><h1 class="mt-2 text-3xl font-extrabold text-slate-900">إعدادات النشاط التجاري</h1><p class="mt-2 text-sm text-slate-600">اضبط هوية النشاط والضرائب والإيصالات والإعدادات التشغيلية.</p></div>
        @if($errors->any())<div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert"><p class="font-bold">يرجى مراجعة البيانات التالية:</p><ul class="mt-2 list-disc space-y-1 pr-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        @if(($onboarding ?? false))<div class="rounded-xl border border-indigo-200 bg-indigo-50 p-4 text-sm font-bold text-indigo-800">الخطوة ١ من ٣: أكمِل إعدادات نشاطك التجاري.</div>@endif
        <form method="POST" action="{{ $formAction ?? route('business.settings.update') }}" class="space-y-6">
            @csrf @method('PUT')
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7" aria-labelledby="identity-heading">
                <div class="flex items-start gap-3"><span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-indigo-50 text-2xl text-indigo-600"><i class="bx bx-buildings" aria-hidden="true"></i></span><div><h2 id="identity-heading" class="text-xl font-extrabold text-slate-900">هوية النشاط</h2><p class="mt-1 text-sm text-slate-500">المعلومات التي تظهر على شاشات النظام والإيصالات.</p></div></div>
                <div class="mt-6 grid gap-5 md:grid-cols-2">
                    <div><label for="display-name" class="text-sm font-bold text-slate-700">اسم العرض</label><input id="display-name" name="display_name" value="{{ old('display_name', $settings->display_name) }}" required autocomplete="organization" class="settings-input"></div>
                    <div><label for="legal-name" class="text-sm font-bold text-slate-700">الاسم القانوني</label><input id="legal-name" name="legal_name" value="{{ old('legal_name', $settings->legal_name) }}" autocomplete="organization" class="settings-input"></div>
                    <div class="md:col-span-2"><label for="address" class="text-sm font-bold text-slate-700">العنوان</label><textarea id="address" name="address" rows="3" autocomplete="street-address" class="settings-input">{{ old('address', $settings->address) }}</textarea></div>
                    <div><label for="business-phone" class="text-sm font-bold text-slate-700">الهاتف</label><input id="business-phone" name="phone" value="{{ old('phone', $settings->phone) }}" autocomplete="tel" class="settings-input"></div>
                    <div><label for="business-email" class="text-sm font-bold text-slate-700">البريد الإلكتروني</label><input id="business-email" name="email" type="email" value="{{ old('email', $settings->email) }}" autocomplete="email" class="settings-input" dir="ltr"></div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7" aria-labelledby="tax-heading">
                <div class="flex items-start gap-3"><span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-amber-50 text-2xl text-amber-600"><i class="bx bx-receipt" aria-hidden="true"></i></span><div><h2 id="tax-heading" class="text-xl font-extrabold text-slate-900">العملة والضريبة</h2><p class="mt-1 text-sm text-slate-500">إعدادات الحساب المالي الأساسية.</p></div></div>
                <div class="mt-6 grid gap-5 md:grid-cols-3">
                    <div><label for="timezone" class="text-sm font-bold text-slate-700">المنطقة الزمنية</label><input id="timezone" name="timezone" value="{{ old('timezone', $settings->timezone) }}" required autocomplete="off" class="settings-input" dir="ltr"></div>
                    <div><label for="currency-code" class="text-sm font-bold text-slate-700">العملة</label><select id="currency-code" name="currency_code" class="settings-input">@foreach($currencies as $currency)<option value="{{ $currency }}" @selected(old('currency_code', $settings->currency_code) === $currency)>{{ $currency }}</option>@endforeach</select></div>
                    <div><label for="vat-rate" class="text-sm font-bold text-slate-700">نسبة ضريبة القيمة المضافة</label><div class="relative"><input id="vat-rate" name="vat_rate" type="number" min="0" max="100" step="0.01" value="{{ old('vat_rate', $settings->vat_rate) }}" required class="settings-input text-right" dir="ltr"><span class="pointer-events-none absolute left-4 top-3.5 text-sm font-bold text-slate-400">%</span></div></div>
                </div>
                <input type="hidden" name="vat_enabled" value="1"><input type="hidden" name="vat_mode" value="inclusive"><p class="mt-5 flex items-center gap-2 rounded-xl bg-slate-50 p-4 text-sm text-slate-600"><i class="bx bx-info-circle text-lg text-indigo-500" aria-hidden="true"></i>الأسعار شاملة الضريبة في هذا الإصدار.</p>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7" aria-labelledby="receipt-heading">
                <div class="flex items-start gap-3"><span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-emerald-50 text-2xl text-emerald-600"><i class="bx bx-printer" aria-hidden="true"></i></span><div><h2 id="receipt-heading" class="text-xl font-extrabold text-slate-900">الإيصالات والتشغيل</h2><p class="mt-1 text-sm text-slate-500">خصص شكل الإيصال والتنبيهات التشغيلية.</p></div></div>
                <div class="mt-6 grid gap-5 md:grid-cols-2">
                    <div><label for="receipt-prefix" class="text-sm font-bold text-slate-700">بادئة رقم الإيصال</label><input id="receipt-prefix" name="receipt_prefix" value="{{ old('receipt_prefix', $settings->receipt_prefix) }}" required autocomplete="off" class="settings-input" dir="ltr"></div>
                    <div><label for="low-stock-threshold" class="text-sm font-bold text-slate-700">حد التنبيه للمخزون</label><input id="low-stock-threshold" name="low_stock_threshold" type="number" min="0" value="{{ old('low_stock_threshold', $settings->low_stock_threshold) }}" required class="settings-input" dir="ltr"></div>
                    <div><label for="receipt-header" class="text-sm font-bold text-slate-700">رأس الإيصال</label><textarea id="receipt-header" name="receipt_header" rows="3" class="settings-input">{{ old('receipt_header', $settings->receipt_header) }}</textarea></div>
                    <div><label for="receipt-footer" class="text-sm font-bold text-slate-700">تذييل الإيصال</label><textarea id="receipt-footer" name="receipt_footer" rows="3" class="settings-input">{{ old('receipt_footer', $settings->receipt_footer) }}</textarea></div>
                </div>
                <fieldset class="mt-6 grid gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700 sm:grid-cols-2"><legend class="px-2 text-sm font-bold text-slate-800">خيارات الإيصال</legend>@foreach([['receipt_show_cashier','إظهار الكاشير',$settings->receipt_show_cashier],['receipt_show_date','إظهار التاريخ',$settings->receipt_show_date],['receipt_show_tax_breakdown','إظهار تفاصيل الضريبة',$settings->receipt_show_tax_breakdown],['allow_negative_stock','السماح بالمخزون السالب',$settings->allow_negative_stock]] as [$name,$label,$checked])<label class="flex min-h-11 items-center gap-3 rounded-lg px-2 font-medium hover:bg-white"><input type="hidden" name="{{ $name }}" value="0"><input type="checkbox" name="{{ $name }}" value="1" @checked(old($name, $checked)) class="h-5 w-5 rounded border-slate-300 text-indigo-600 focus:ring-4 focus:ring-indigo-100">{{ $label }}</label>@endforeach</fieldset>
            </section>
            <div class="flex justify-end"><button class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-7 py-3.5 text-sm font-bold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-200"><i class="bx bx-save" aria-hidden="true"></i>حفظ الإعدادات</button></div>
        </form>
    </div>
    <style>.settings-input{margin-top:.5rem;display:block;width:100%;min-height:3rem;border-radius:.75rem;border:1px solid rgb(203 213 225);background:#fff;padding:.75rem 1rem;color:rgb(15 23 42);box-shadow:0 1px 2px rgb(15 23 42 / .04);outline:2px solid transparent;outline-offset:2px;transition:border-color .15s,box-shadow .15s}.settings-input:focus{border-color:rgb(99 102 241);box-shadow:0 0 0 4px rgb(224 231 255)}.settings-input::placeholder{color:rgb(148 163 184)}select.settings-input{padding-left:1rem;padding-right:1rem}</style>
@endsection
