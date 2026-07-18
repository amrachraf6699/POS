@extends('layouts.app')

@section('title', 'تسجيل الدخول')

@section('navigation')
    <div class="text-lg font-bold text-slate-900">نظام نقاط البيع</div>
    <a href="{{ route('register') }}" class="text-sm font-semibold text-indigo-600">إنشاء حساب</a>
@endsection

@section('content')
    <div class="mx-auto max-w-md">
        <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
            <h1 class="text-2xl font-bold text-slate-900">تسجيل الدخول</h1>
            <p class="mt-2 text-sm text-slate-600">سجل الدخول للمتابعة إلى مساحة العمل.</p>

            @if ($errors->any())
                <div class="mt-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-5">
                @csrf
                <label class="block text-sm font-medium text-slate-700">البريد الإلكتروني
                    <input name="email" type="email" value="{{ old('email') }}" required class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3">
                </label>
                <label class="block text-sm font-medium text-slate-700">كلمة المرور
                    <input name="password" type="password" required class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3">
                </label>
                <button class="w-full rounded-xl bg-indigo-600 px-4 py-3 font-semibold text-white hover:bg-indigo-700">تسجيل الدخول</button>
            </form>
        </div>
    </div>
@endsection
