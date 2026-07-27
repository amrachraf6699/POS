@extends('layouts.app')

@section('title', 'دعوات الفريق')

@section('content')
    <div class="space-y-6">
        <div class="flex items-end justify-between border-b border-slate-200 pb-5">
            <div><p class="text-sm font-bold text-indigo-600">إدارة الفريق</p><h1 class="mt-1 text-3xl font-extrabold text-slate-900">دعوات الفريق</h1></div>
            <a href="{{ route('tenant.staff.index') }}" class="text-sm font-bold text-indigo-600 hover:underline">أعضاء الفريق</a>
        </div>
        @if($errors->any())<div class="rounded-xl bg-red-50 p-4 text-red-700" role="alert">{{ $errors->first() }}</div>@endif
        <section class="rounded-2xl border border-slate-200 bg-white p-6">
            <h2 class="text-lg font-extrabold">دعوة عضو جديد</h2>
            <form method="POST" action="{{ route('tenant.invitations.store') }}" class="mt-5 grid gap-4 sm:grid-cols-[1fr_220px_auto] sm:items-end">
                @csrf
                <label class="block text-sm font-bold">البريد الإلكتروني<input name="email" type="email" value="{{ old('email') }}" required dir="ltr" class="mt-2 block w-full rounded-lg border-slate-300"></label>
                <label class="block text-sm font-bold">الدور<select name="role" required class="mt-2 block w-full rounded-lg border-slate-300">@foreach($assignableRoles as $role)<option value="{{ $role }}" @selected(old('role') === $role)>{{ $role }}</option>@endforeach</select></label>
                <button class="rounded-lg bg-indigo-600 px-5 py-2.5 font-bold text-white">إرسال الدعوة</button>
            </form>
        </section>
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
            @forelse($invitations as $invitation)
                <div class="flex items-center justify-between gap-4 border-b border-slate-100 p-5 last:border-0"><div><p class="font-bold" dir="ltr">{{ $invitation->email }}</p><p class="text-sm text-slate-500">{{ $invitation->role }} · {{ $invitation->status }}</p></div>@if($invitation->isPending())<div class="flex gap-2"><form method="POST" action="{{ route('tenant.invitations.resend', $invitation) }}">@csrf<button class="rounded-lg bg-slate-100 px-3 py-2 text-sm font-bold">إعادة إرسال</button></form><form method="POST" action="{{ route('tenant.invitations.revoke', $invitation) }}">@csrf<button class="rounded-lg bg-red-50 px-3 py-2 text-sm font-bold text-red-700">إلغاء</button></form></div>@endif</div>
            @empty
                <p class="p-10 text-center text-slate-500">لا توجد دعوات بعد.</p>
            @endforelse
        </section>
    </div>
@endsection
