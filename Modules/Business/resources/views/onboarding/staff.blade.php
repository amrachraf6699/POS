@extends('layouts.app')
@section('title', 'دعوة الفريق')
@section('content')
<div class="mx-auto max-w-2xl space-y-6"><div><p class="text-sm font-bold text-indigo-600">الخطوة ٣ من ٣</p><h1 class="mt-2 text-3xl font-extrabold text-slate-900">هل تريد دعوة فريقك الآن؟</h1><p class="mt-2 text-sm text-slate-600">هذه الخطوة اختيارية ويمكنك إرسال الدعوات من صفحة الفريق لاحقاً.</p></div><div class="flex flex-wrap gap-3"><a href="{{ route('tenant.invitations.index') }}" class="rounded-xl bg-indigo-600 px-6 py-3 font-bold text-white">دعوة عضو</a><form method="POST" action="{{ route('business.onboarding.staff.skip') }}">@csrf<button class="rounded-xl border border-slate-300 px-6 py-3 font-bold text-slate-700">تخطي الآن</button></form></div></div>
@endsection
