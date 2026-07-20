@extends('layouts.auth')

@section('title', 'إنشاء حساب جديد')

@section('content')
    <div class="mt-8">
        <h1 class="text-center text-3xl font-extrabold text-[#18213d]">ابدأ بإدارة نشاطك اليوم</h1>
        <p class="mt-3 text-center text-base text-[#8a93a8]">أنشئ حسابك ومساحة العمل في دقائق</p>
        @if ($errors->any())
            <div class="mt-7 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert">@foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>
        @endif
        <form method="post" action="{{ route('register.store') }}" class="mt-7 space-y-4">
            @csrf
            <label for="name" class="block text-sm font-medium text-[#25304a]">الاسم<input id="name" name="name" value="{{ old('name') }}" required autocomplete="name" class="mt-2 h-12 block w-full rounded-xl border border-[#d7dce8] px-4 outline-none focus:border-[#4450d5] focus:ring-4 focus:ring-indigo-100"></label>
            <label for="email" class="block text-sm font-medium text-[#25304a]">البريد الإلكتروني<input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="mt-2 h-12 block w-full rounded-xl border border-[#d7dce8] px-4 outline-none focus:border-[#4450d5] focus:ring-4 focus:ring-indigo-100"></label>
            <label for="tenant_name" class="block text-sm font-medium text-[#25304a]">اسم النشاط التجاري<input id="tenant_name" name="tenant_name" value="{{ old('tenant_name') }}" required autocomplete="organization" class="mt-2 h-12 block w-full rounded-xl border border-[#d7dce8] px-4 outline-none focus:border-[#4450d5] focus:ring-4 focus:ring-indigo-100"></label>
            <label for="password" class="block text-sm font-medium text-[#25304a]">كلمة المرور<input id="password" type="password" name="password" required autocomplete="new-password" class="mt-2 h-12 block w-full rounded-xl border border-[#d7dce8] px-4 outline-none focus:border-[#4450d5] focus:ring-4 focus:ring-indigo-100"></label>
            <label for="password_confirmation" class="block text-sm font-medium text-[#25304a]">تأكيد كلمة المرور<input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="mt-2 h-12 block w-full rounded-xl border border-[#d7dce8] px-4 outline-none focus:border-[#4450d5] focus:ring-4 focus:ring-indigo-100"></label>
            <button type="submit" class="h-14 w-full rounded-xl bg-[#4450d5] text-lg font-bold text-white shadow-lg shadow-indigo-200 transition hover:bg-[#3540c2]">إنشاء الحساب ومساحة العمل</button>
        </form>
        <p class="mt-6 text-center text-sm text-[#68748c]">لديك حساب بالفعل؟ <a href="{{ route('login') }}" class="font-bold text-[#4450d5]">تسجيل الدخول</a></p>
    </div>
@endsection
