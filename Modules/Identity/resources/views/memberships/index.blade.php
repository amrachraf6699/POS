@extends('layouts.app')

@section('title', 'إدارة الفريق')

@section('content')
    <div class="space-y-6">
        <div class="flex items-end justify-between border-b border-slate-200 pb-5">
            <div><p class="text-sm font-bold text-indigo-600">إدارة المستخدمين</p><h1 class="mt-1 text-3xl font-extrabold text-slate-900">أعضاء الفريق</h1></div>
            @can('users.invite')<a href="{{ route('tenant.invitations.index') }}" class="rounded-xl bg-indigo-600 px-4 py-3 text-sm font-bold text-white">دعوة عضو</a>@endcan
        </div>
        @if($errors->any())<div class="rounded-xl border border-red-200 bg-red-50 p-4 text-red-700" role="alert">{{ $errors->first() }}</div>@endif
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
            @foreach($memberships as $membership)
                <form method="POST" action="{{ route('tenant.staff.update', $membership) }}" class="grid gap-3 border-b border-slate-100 p-5 last:border-0 md:grid-cols-[1fr_180px_150px_auto] md:items-center">
                    @csrf @method('PATCH')
                    <div><p class="font-bold text-slate-900">{{ $membership->user->name }}</p><p class="text-sm text-slate-500" dir="ltr">{{ $membership->user->email }}</p></div>
                    <select name="role" class="rounded-lg border-slate-300" @disabled(!auth()->user()->can('users.update'))>@foreach(\Modules\Identity\App\Models\Membership::roles() as $role)<option value="{{ $role }}" @selected($membership->role === $role)>{{ $role }}</option>@endforeach</select>
                    <select name="status" class="rounded-lg border-slate-300" @disabled(!auth()->user()->can('users.update'))><option value="active" @selected($membership->status === 'active')>نشط</option><option value="inactive" @selected($membership->status === 'inactive')>غير نشط</option></select>
                    @can('users.update')<button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-bold text-white">حفظ</button>@endcan
                </form>
            @endforeach
        </div>
    </div>
@endsection
