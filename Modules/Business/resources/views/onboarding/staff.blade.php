@extends('layouts.app')

@section('title', 'دعوة الفريق')

@section('content')
    <div class="mx-auto max-w-6xl overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[0_18px_55px_rgba(30,41,59,.08)]">
        <x-business::onboarding-progress :step="3" />
        <div class="grid lg:grid-cols-[1fr_.8fr]">
            <div class="p-6 sm:p-10"><div class="max-w-xl"><span class="grid h-14 w-14 place-items-center rounded-2xl bg-indigo-50 text-3xl text-indigo-600"><i class="bx bx-user-plus" aria-hidden="true"></i></span><h2 class="mt-6 text-2xl font-extrabold text-slate-900">هل تريد دعوة فريقك الآن؟</h2><p class="mt-3 max-w-lg text-sm leading-7 text-slate-500">هذه الخطوة اختيارية. يمكنك إرسال دعوة للكاشير أو مسؤول المخزون الآن، أو إكمالها لاحقاً من صفحة الفريق.</p><div class="mt-8 flex flex-wrap items-center gap-3"><a href="{{ route('tenant.invitations.index') }}" class="inline-flex min-h-12 items-center gap-2 rounded-xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-200">دعوة عضو <i class="bx bx-left-arrow-alt text-xl" aria-hidden="true"></i></a><form method="POST" action="{{ route('business.onboarding.staff.skip') }}">@csrf<button class="min-h-12 rounded-xl border border-slate-300 px-6 py-3 text-sm font-bold text-slate-600 transition hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-slate-100">تخطي الآن</button></form></div><p class="mt-6 flex items-center gap-2 text-xs text-slate-400"><i class="bx bx-info-circle text-base" aria-hidden="true"></i>لن تتعطل عملية الإعداد إذا اخترت التخطي.</p></div></div>
            <x-business::onboarding-illustration title="فريق جاهز للعمل" description="أضف أعضاء فريقك بالصلاحية المناسبة، ثم ابدأ العمل بثقة." icon="bx-group" />
        </div>
    </div>
@endsection
