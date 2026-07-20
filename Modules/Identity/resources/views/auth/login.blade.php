@extends('layouts.auth')

@section('title', 'تسجيل الدخول')

@section('content')
    <div class="mt-10">
        <h1 class="text-center text-3xl font-extrabold text-[#18213d]">مرحباً بك مجدداً!</h1>
        <p class="mt-3 text-center text-base text-[#8a93a8]">سجل الدخول لإدارة مبيعاتك ومخزونك بسهولة</p>

        @if ($errors->any())
            <div class="mt-7 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert">
                @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-5">
            @csrf
            <label for="email" class="block text-sm font-medium text-[#25304a]">البريد الإلكتروني
                <span class="relative mt-2 block"><input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="info@business.com" required autocomplete="email" class="h-14 w-full rounded-xl border border-[#d7dce8] bg-white px-5 pl-14 text-left text-base outline-none transition placeholder:text-[#b6bdcc] focus:border-[#4450d5] focus:ring-4 focus:ring-indigo-100"><svg class="pointer-events-none absolute left-4 top-4 h-6 w-6 text-[#a5aec0]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 7 8 6 8-6"/></svg></span>
            </label>
            <label for="password" class="block text-sm font-medium text-[#25304a]">كلمة المرور
                <span class="relative mt-2 block"><input id="password" name="password" type="password" placeholder="أدخل كلمة المرور" required autocomplete="current-password" class="h-14 w-full rounded-xl border border-[#d7dce8] bg-white px-5 pl-14 text-right text-base outline-none transition placeholder:text-[#b6bdcc] focus:border-[#4450d5] focus:ring-4 focus:ring-indigo-100"><svg class="pointer-events-none absolute left-4 top-4 h-6 w-6 text-[#a5aec0]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><rect x="5" y="10" width="14" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg></span>
            </label>
            <div class="flex items-center justify-between text-sm"><label class="flex items-center gap-2 text-[#4d5870]"><input type="checkbox" name="remember" class="h-5 w-5 rounded border-[#cbd2e0] text-[#4450d5] focus:ring-[#4450d5]"> تذكرني</label><a href="#" class="font-medium text-[#4450d5]">نسيت كلمة المرور؟</a></div>
            <button type="submit" class="h-14 w-full rounded-xl bg-[#4450d5] text-lg font-bold text-white shadow-lg shadow-indigo-200 transition hover:bg-[#3540c2]">تسجيل الدخول</button>
        </form>

        <div class="my-7 flex items-center gap-4 text-sm text-[#9aa3b5]"><span class="h-px flex-1 bg-[#e5e8ef]"></span><span>أو</span><span class="h-px flex-1 bg-[#e5e8ef]"></span></div>
        <button type="button" class="flex h-14 w-full items-center justify-center gap-3 rounded-xl border border-[#d7dce8] text-base text-[#25304a]"><span class="text-xl font-bold text-[#4285f4]">G</span>المتابعة باستخدام Google</button>
        <p class="mt-7 text-center text-sm text-[#68748c]">ليس لديك حساب؟ <a href="{{ route('register') }}" class="font-bold text-[#4450d5]">إنشاء حساب جديد</a></p>
    </div>
@endsection
