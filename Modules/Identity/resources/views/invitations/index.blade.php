@extends('layouts.app')

@section('title', 'دعوات الفريق')

@section('navigation')<div class="text-lg font-bold text-slate-900">نظام نقاط البيع</div>@endsection

@section('content')
    <div class="space-y-8">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">دعوات الفريق</h1>
            <p class="mt-2 text-slate-600">أرسل دعوات للمديرين وتابع حالتها.</p>
        </div>
        @if (session('status'))<div class="rounded-xl bg-emerald-50 p-4 text-emerald-700">{{ session('status') }}</div>@endif
        <form method="POST" action="{{ route('tenant.invitations.store') }}" class="flex gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            @csrf
            <input name="email" type="email" placeholder="البريد الإلكتروني" required class="flex-1 rounded-xl border-slate-300 px-4 py-3">
            <button class="rounded-xl bg-indigo-600 px-5 py-3 font-semibold text-white">إرسال دعوة</button>
        </form>
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            @forelse ($invitations as $invitation)
                <div class="flex items-center justify-between border-b border-slate-100 p-5 last:border-0">
                    <div><p class="font-semibold text-slate-900">{{ $invitation->email }}</p><p class="text-sm text-slate-500">{{ $invitation->status }} · {{ optional($invitation->expires_at)->format('Y-m-d H:i') }}</p></div>
                    <div class="flex gap-2">
                        @if ($invitation->status === 'pending')
                            <form method="POST" action="{{ route('tenant.invitations.resend', $invitation) }}">@csrf<button class="rounded-lg bg-slate-100 px-3 py-2 text-sm font-semibold">إعادة إرسال</button></form>
                            <form method="POST" action="{{ route('tenant.invitations.revoke', $invitation) }}">@csrf<button class="rounded-lg bg-red-50 px-3 py-2 text-sm font-semibold text-red-700">إلغاء</button></form>
                        @endif
                    </div>
                </div>
            @empty
                <p class="p-6 text-slate-500">لا توجد دعوات بعد.</p>
            @endforelse
        </div>
    </div>
@endsection
