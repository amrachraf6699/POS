@extends('layouts.auth')

@section('title', 'قبول الدعوة')

@section('navigation')<div class="text-lg font-bold text-slate-900">نظام نقاط البيع</div>@endsection

@section('content')
    <div class="mx-auto max-w-md rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-900">إنشاء حساب للانضمام</h1>
        <p class="mt-2 text-sm text-slate-600">أكمل بياناتك للانضمام إلى {{ $invitation->tenant->name }} كمدير.</p>
        <form method="POST" action="{{ URL::temporarySignedRoute('invitations.accept.store', $invitation->expires_at, ['invitation' => $invitation, 'token' => $token]) }}" class="mt-6 space-y-5">
            @csrf
            <label class="block text-sm font-medium text-slate-700">الاسم
                <input name="name" value="{{ old('name') }}" required class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3">
            </label>
            <p class="rounded-xl bg-slate-50 p-3 text-sm text-slate-600">البريد: {{ $invitation->email }}</p>
            <label class="block text-sm font-medium text-slate-700">كلمة المرور
                <input name="password" type="password" required class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3">
            </label>
            <label class="block text-sm font-medium text-slate-700">تأكيد كلمة المرور
                <input name="password_confirmation" type="password" required class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3">
            </label>
            <button class="w-full rounded-xl bg-indigo-600 px-4 py-3 font-semibold text-white">قبول الدعوة</button>
        </form>
    </div>
@endsection
