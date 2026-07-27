@extends('layouts.app')

@section('title', 'إعداد الفرع الأول')

@section('content')
    <div class="mx-auto max-w-6xl overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[0_18px_55px_rgba(30,41,59,.08)]">
        <x-business::onboarding-progress :step="2" />
        <div class="grid lg:grid-cols-[1fr_.8fr]">
            <div class="p-6 sm:p-10">
                <div class="max-w-xl">
                    <h2 class="text-2xl font-extrabold text-slate-900">أنشئ أول فرع نشط</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-500">أضف بيانات فرعك الرئيسي الآن. يمكنك تعديلها وإضافة فروع أخرى لاحقاً.</p>
                    @if ($errors->any())
                        <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert"><p class="font-bold">يرجى مراجعة البيانات التالية:</p><ul class="mt-2 list-disc space-y-1 pr-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                    @endif
                    <form method="POST" action="{{ route('business.onboarding.branch.save') }}" class="mt-8 grid gap-5 sm:grid-cols-2">@csrf
                        <div><label for="branch-name" class="text-sm font-bold text-slate-700">اسم الفرع <span class="text-red-500">*</span></label><input id="branch-name" name="name" value="{{ old('name') }}" required autocomplete="organization" class="onboarding-input"></div>
                        <div><label for="branch-code" class="text-sm font-bold text-slate-700">رمز الفرع <span class="text-red-500">*</span></label><input id="branch-code" name="code" value="{{ old('code') }}" required autocomplete="off" class="onboarding-input" dir="ltr"></div>
                        <div><label for="branch-city" class="text-sm font-bold text-slate-700">المدينة</label><input id="branch-city" name="city" value="{{ old('city') }}" autocomplete="address-level2" class="onboarding-input"></div>
                        <div><label for="branch-phone" class="text-sm font-bold text-slate-700">الهاتف</label><input id="branch-phone" name="phone" value="{{ old('phone') }}" autocomplete="tel" class="onboarding-input" dir="ltr"></div>
                        <input type="hidden" name="country_code" value="EG"><input type="hidden" name="timezone" value="Africa/Cairo">
                        <div class="mt-3 flex items-center gap-4 sm:col-span-2"><button class="inline-flex min-h-12 items-center gap-2 rounded-xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-200"><span>حفظ ومتابعة</span><i class="bx bx-left-arrow-alt text-xl" aria-hidden="true"></i></button><span class="text-xs text-slate-400">سيصبح هذا الفرع نشطاً فور الحفظ.</span></div>
                    </form>
                </div>
            </div>
            <x-business::onboarding-illustration title="فرعك يبدأ من هنا" description="نبدأ بالفرع الرئيسي حتى تستطيع إدارة المبيعات والفريق من مكان واحد." icon="bx-store-alt" />
        </div>
    </div>
    <style>.onboarding-input{margin-top:.5rem;display:block;min-height:3rem;width:100%;border-radius:.75rem;border:1px solid rgb(203 213 225);background:#fff;padding:.75rem 1rem;color:rgb(15 23 42);outline:2px solid transparent;outline-offset:2px;transition:border-color .15s,box-shadow .15s}.onboarding-input:focus{border-color:rgb(99 102 241);box-shadow:0 0 0 4px rgb(224 231 255)}</style>
@endsection
