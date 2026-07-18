@extends('layouts.app')

@section('title', 'تسجيل الدخول لقبول الدعوة')

@section('navigation')<div class="text-lg font-bold text-slate-900">نظام نقاط البيع</div>@endsection

@section('content')
    <div class="mx-auto max-w-md rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm">
        <h1 class="text-2xl font-bold text-slate-900">سجل الدخول أولاً</h1>
        <p class="mt-3 text-slate-600">هذا البريد لديه حساب بالفعل. سجل الدخول بالبريد المدعو ثم افتح رابط الدعوة مرة أخرى.</p>
        <a href="{{ route('login', ['url' => request()->fullUrl()]) }}" class="mt-6 inline-flex rounded-xl bg-indigo-600 px-5 py-3 font-semibold text-white">تسجيل الدخول</a>
    </div>
@endsection
