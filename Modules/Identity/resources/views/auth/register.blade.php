@extends('layouts.app')

@section('title', 'إنشاء حساب المالك')

@section('navigation')
    <span class="text-sm font-semibold text-slate-800">نظام نقاط البيع</span>
@endsection

@section('content')
    <div class="mx-auto max-w-xl">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <p class="text-sm font-medium text-indigo-600">ابدأ مساحة العمل الخاصة بك</p>
            <h1 class="mt-2 text-2xl font-bold text-slate-900">إنشاء حساب المالك</h1>
            <p class="mt-2 text-sm leading-6 text-slate-500">أنشئ حسابك وبيانات نشاطك التجاري في خطوة واحدة.</p>

            @if ($errors->any())
                <div class="mt-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert">
                    <p class="font-semibold">يرجى مراجعة البيانات المدخلة.</p>
                    <ul class="mt-2 list-disc space-y-1 pr-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="post" action="{{ route('register.store') }}" class="mt-6 space-y-5">
                @csrf
                <div><label for="name" class="block text-sm font-medium text-slate-700">الاسم</label><input id="name" name="name" value="{{ old('name') }}" required autocomplete="name" class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"></div>
                <div><label for="email" class="block text-sm font-medium text-slate-700">البريد الإلكتروني</label><input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"></div>
                <div><label for="tenant_name" class="block text-sm font-medium text-slate-700">اسم النشاط التجاري</label><input id="tenant_name" name="tenant_name" value="{{ old('tenant_name') }}" required autocomplete="organization" class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"><p class="mt-1 text-xs text-slate-500">سيتم إنشاء المعرّف الداخلي تلقائياً.</p></div>
                <div><label for="password" class="block text-sm font-medium text-slate-700">كلمة المرور</label><input id="password" type="password" name="password" required autocomplete="new-password" class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"></div>
                <div><label for="password_confirmation" class="block text-sm font-medium text-slate-700">تأكيد كلمة المرور</label><input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"></div>
                <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-3 text-sm font-semibold text-white hover:bg-indigo-500">إنشاء الحساب ومساحة العمل</button>
            </form>
        </div>
    </div>
@endsection
