@extends('layouts.app')

@section('title', 'أعضاء الفريق')

@section('content')
    @php($managerRoles = [\Modules\Identity\App\Models\Membership::ROLE_CASHIER, \Modules\Identity\App\Models\Membership::ROLE_INVENTORY_STAFF])
    <div class="space-y-6">
        <div class="flex items-end justify-between border-b border-slate-200 pb-5">
            <div>
                <p class="text-sm font-bold text-indigo-600">إدارة المستخدمين</p>
                <h1 class="mt-1 text-3xl font-extrabold">أعضاء الفريق</h1>
            </div><a href="{{ route('tenant.invitations.index') }}"
                class="rounded-xl bg-indigo-600 px-4 py-3 text-sm font-bold text-white">دعوة عضو</a>
        </div>
        @if ($errors->any())
            <div class="rounded-xl bg-red-50 p-4 text-red-700" role="alert">{{ $errors->first() }}</div>
        @endif
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
            @foreach ($memberships as $membership)
                @php($canManage = $actorMembership->isOwner() || (!$membership->isOwner() && !$membership->isManager()))
                <form method="POST" action="{{ route('tenant.staff.update', $membership) }}"
                    class="grid gap-3 border-b border-slate-100 p-5 last:border-0 md:grid-cols-[1fr_190px_150px_auto] md:items-center">
                    @csrf @method('PATCH')
                    <div>
                        <p class="font-bold">{{ $membership->user->name }}</p>
                        <p class="text-sm text-slate-500" dir="ltr">{{ $membership->user->email }}</p>
                    </div>
                    @if ($canManage)
                        <select name="role" class="rounded-lg border-slate-300">
                            @foreach ($actorMembership->isOwner() ? \Modules\Identity\App\Models\Membership::roles() : $managerRoles as $role)
                                <option value="{{ $role }}" @selected($membership->role === $role)>{{ $role }}
                                </option>
                            @endforeach
                        </select><select name="status" class="rounded-lg border-slate-300">
                            <option value="active" @selected($membership->status === 'active')>نشط</option>
                            <option value="inactive" @selected($membership->status === 'inactive')>غير نشط</option>
                        </select><button
                        class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-bold text-white">حفظ</button>@else<input
                            type="hidden" name="role" value="{{ $membership->role }}"><input type="hidden"
                            name="status" value="{{ $membership->status }}"><span
                            class="text-sm text-slate-500">{{ $membership->role }} · {{ $membership->status }}</span>
                    @endif
                </form>
            @endforeach
        </section>
    </div>
@endsection
