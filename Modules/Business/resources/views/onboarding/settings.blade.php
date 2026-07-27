@extends('layouts.app')

@section('title', 'إعداد النشاط التجاري')

@section('content')
    <div class="mx-auto max-w-[1260px] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-[0_14px_34px_rgba(15,23,42,.07)]">
        <x-business::onboarding-progress :step="1" />
        <form method="POST" action="{{ route('business.onboarding.settings.save') }}">@csrf @method('PUT')
            <div class="grid lg:grid-cols-[1.05fr_.95fr]">
                <div class="order-2 border-t border-slate-100 p-6 sm:p-10 lg:order-1 lg:border-l lg:border-t-0">
                    <x-business::onboarding-illustration title="كل إعداداتك في مكان واحد" description="أدخل بيانات نشاطك الأساسية. يمكنك تعديلها لاحقاً من الإعدادات في أي وقت." />
                </div>
                <section class="order-1 p-6 sm:p-10 lg:order-2">
                    <div class="mx-auto max-w-[560px]">
                        @if ($errors->any())
                            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert"><p class="font-bold">يرجى مراجعة البيانات التالية:</p><ul class="mt-2 list-disc space-y-1 pr-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                        @endif
                        <div class="space-y-6">
                            <div><label for="display-name" class="onboarding-label">اسم النشاط <span class="text-red-500">*</span></label><div class="relative"><input id="display-name" name="display_name" value="{{ old('display_name', $settings->display_name) }}" required autocomplete="organization" class="onboarding-control pl-12"><i class="bx bx-store absolute bottom-3.5 left-4 text-xl text-slate-400" aria-hidden="true"></i></div><p class="onboarding-hint">سيظهر هذا الاسم في الفواتير والتقارير.</p></div>
                            <div><label for="timezone" class="onboarding-label">المنطقة الزمنية <span class="text-red-500">*</span></label><div class="relative"><select id="timezone" name="timezone" class="onboarding-control appearance-none pl-12" dir="rtl"><option value="Africa/Cairo" @selected(old('timezone', $settings->timezone) === 'Africa/Cairo')>القاهرة (GMT+02:00)</option><option value="Asia/Riyadh" @selected(old('timezone', $settings->timezone) === 'Asia/Riyadh')>الرياض (GMT+03:00)</option></select><i class="bx bx-globe absolute bottom-3.5 left-4 text-xl text-slate-400" aria-hidden="true"></i></div><p class="onboarding-hint">تُستخدم لتسجيل الوقت والتقارير.</p></div>
                            <div><label for="currency-code" class="onboarding-label">العملة <span class="text-red-500">*</span></label><div class="relative"><select id="currency-code" name="currency_code" class="onboarding-control appearance-none pl-12" dir="rtl">@foreach($currencies as $currency)<option value="{{ $currency }}" @selected(old('currency_code', $settings->currency_code) === $currency)>{{ $currency === 'EGP' ? 'جنيه مصري (ج.م)' : $currency }}</option>@endforeach</select><i class="bx bx-money absolute bottom-3.5 left-4 text-xl text-slate-400" aria-hidden="true"></i></div><p class="onboarding-hint">العملة الأساسية لجميع معاملاتك.</p></div>
                            <div><label for="vat-rate" class="onboarding-label">ضريبة القيمة المضافة <span class="text-red-500">*</span></label><div class="relative"><input id="vat-rate" name="vat_rate" type="number" min="0" max="100" step="0.01" value="{{ old('vat_rate', $settings->vat_rate) }}" required class="onboarding-control pl-12" dir="ltr"><span class="absolute bottom-3.5 left-5 font-extrabold text-slate-500">%</span></div><p class="onboarding-hint">النسبة الافتراضية للضريبة على المبيعات.</p></div>
                        </div>
                        <input type="hidden" name="legal_name" value="{{ $settings->legal_name }}"><input type="hidden" name="address" value="{{ $settings->address }}"><input type="hidden" name="phone" value="{{ $settings->phone }}"><input type="hidden" name="email" value="{{ $settings->email }}"><input type="hidden" name="vat_enabled" value="1"><input type="hidden" name="vat_mode" value="inclusive"><input type="hidden" name="receipt_prefix" value="{{ $settings->receipt_prefix }}"><input type="hidden" name="receipt_header" value="{{ $settings->receipt_header }}"><input type="hidden" name="receipt_footer" value="{{ $settings->receipt_footer }}"><input type="hidden" name="receipt_show_cashier" value="{{ $settings->receipt_show_cashier ? 1 : 0 }}"><input type="hidden" name="receipt_show_date" value="{{ $settings->receipt_show_date ? 1 : 0 }}"><input type="hidden" name="receipt_show_tax_breakdown" value="{{ $settings->receipt_show_tax_breakdown ? 1 : 0 }}"><input type="hidden" name="low_stock_threshold" value="{{ $settings->low_stock_threshold }}"><input type="hidden" name="allow_negative_stock" value="{{ $settings->allow_negative_stock ? 1 : 0 }}">
                    </div>
                </section>
            </div>
            <footer class="flex flex-wrap items-center justify-between gap-5 border-t border-slate-200 px-6 py-6 sm:px-10"><p class="text-xs text-slate-400">تستطيع تحديث الإعدادات التفصيلية لاحقاً من صفحة النشاط.</p><button class="inline-flex min-h-12 items-center gap-3 rounded-xl bg-[#3345c9] px-8 py-3 text-base font-extrabold text-white shadow-sm transition hover:bg-[#2c3bb0] focus:outline-none focus:ring-4 focus:ring-indigo-200"><i class="bx bx-left-arrow-alt text-2xl" aria-hidden="true"></i>حفظ ومتابعة</button></footer>
        </form>
    </div>
    <style>.onboarding-label{display:block;font-size:.875rem;font-weight:800;color:rgb(30 41 59)}.onboarding-control{margin-top:.6rem;display:block;min-height:3rem;width:100%;border-radius:.6rem;border:1px solid rgb(203 213 225);background:#fff;padding:.75rem 1rem;color:rgb(30 41 59);box-shadow:0 1px 2px rgb(15 23 42/.03);outline:2px solid transparent;outline-offset:2px}.onboarding-control:focus{border-color:rgb(79 70 229);box-shadow:0 0 0 4px rgb(224 231 255)}.onboarding-hint{margin-top:.4rem;font-size:.75rem;color:rgb(100 116 139)}</style>
@endsection
