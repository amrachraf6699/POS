@extends('layouts.app')

@section('title', 'قبول الدعوة')

@section('navigation')<div class="text-lg font-bold text-slate-900">نظام نقاط البيع</div>@endsection

@section('content')
    <div class="mx-auto max-w-md rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm">
        <h1 class="text-2xl font-bold text-slate-900">دعوة جديدة</h1>
        <p class="mt-3 text-slate-600">هل تريد الانضمام إلى {{ $invitation->tenant->name }} كمدير؟</p>
        <form method="POST" action="{{ URL::temporarySignedRoute('invitations.accept.store', $invitation->expires_at, ['invitation' => $invitation, 'token' => $token]) }}" class="mt-6">
            @csrf
            <button class="w-full rounded-xl bg-indigo-600 px-4 py-3 font-semibold text-white">قبول الدعوة</button>
        </form>
    </div>
@endsection
