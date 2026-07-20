@extends('layouts.app')

@php($editing = $branch->exists)
@section('title', $editing ? 'تعديل الفرع' : 'إضافة فرع')

@section('navigation')
    <div class="flex w-full items-center justify-between"><a href="{{ route('business.branches.index') }}" class="font-bold text-slate-900">← الفروع</a><span class="text-sm text-slate-500">إدارة الفروع</span></div>
@endsection

@section('content')
    <div class="mx-auto max-w-4xl">
        @if (session('status')) <div class="mb-5 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">{{ session('status') }}</div> @endif
        @if ($errors->any()) <div class="mb-5 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700"><ul class="list-disc space-y-1 pr-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div> @endif
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><h1 class="text-2xl font-bold">{{ $editing ? 'تعديل بيانات الفرع' : 'إضافة فرع جديد' }}</h1>
            <form method="POST" action="{{ $editing ? route('business.branches.update', $branch) : route('business.branches.store') }}" class="mt-6 grid gap-5 md:grid-cols-2">@csrf @if($editing) @method('PUT') @endif
                <div><label class="text-sm font-semibold">اسم الفرع</label><input name="name" value="{{ old('name', $branch->name) }}" required class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2"></div>
                <div><label class="text-sm font-semibold">رمز الفرع</label><input name="code" value="{{ old('code', $branch->code) }}" required class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2"><p class="mt-1 text-xs text-slate-500">أحرف إنجليزية وأرقام وشرطة وشرطة سفلية.</p></div>
                <div><label class="text-sm font-semibold">الهاتف</label><input name="phone" value="{{ old('phone', $branch->phone) }}" class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2"></div>
                <div><label class="text-sm font-semibold">البريد الإلكتروني</label><input type="email" name="email" value="{{ old('email', $branch->email) }}" class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2"></div>
                <div class="md:col-span-2"><label class="text-sm font-semibold">العنوان</label><input name="address_line_1" value="{{ old('address_line_1', $branch->address_line_1) }}" class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2"></div>
                <div><label class="text-sm font-semibold">المدينة</label><input name="city" value="{{ old('city', $branch->city) }}" class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2"></div>
                <div><label class="text-sm font-semibold">المنطقة / المحافظة</label><input name="state" value="{{ old('state', $branch->state) }}" class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2"></div>
                <div><label class="text-sm font-semibold">الدولة</label><input name="country_code" value="{{ old('country_code', $branch->country_code ?: 'EG') }}" maxlength="2" class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2"></div>
                <div><label class="text-sm font-semibold">المنطقة الزمنية</label><input name="timezone" value="{{ old('timezone', $branch->timezone ?: 'Africa/Cairo') }}" required class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2"></div>
                <div class="md:col-span-2"><button class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white">حفظ الفرع</button></div>
            </form>
        </div>
        @if($editing)
            <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><div class="flex items-center justify-between"><div><h2 class="text-xl font-bold">تعيينات الفريق</h2><p class="mt-1 text-sm text-slate-500">يملك المالك وصولاً تلقائياً لكل الفروع النشطة.</p></div>@if($branch->isActive())<span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">نشط</span>@endif</div>
                @if($branch->isActive())<form method="POST" action="{{ route('business.branches.assignments.store', $branch) }}" class="mt-5 flex flex-col gap-3 sm:flex-row">@csrf<select name="user_id" required class="flex-1 rounded-lg border border-slate-300 px-3 py-2"><option value="">اختر عضواً لإسناده</option>@foreach($users as $user)<option value="{{ $user->id }}">{{ $user->name }} — {{ $user->email }}</option>@endforeach</select><button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-bold text-white">تفعيل التعيين</button></form>@endif
                <div class="mt-5 divide-y divide-slate-100">@forelse($branch->assignments as $assignment)<div class="flex items-center justify-between gap-3 py-3"><div><p class="font-semibold">{{ $assignment->user->name }}</p><p class="text-xs text-slate-500">{{ $assignment->user->email }}</p></div><div class="flex items-center gap-2"><span class="text-xs font-bold {{ $assignment->isActive() ? 'text-emerald-600' : 'text-slate-400' }}">{{ $assignment->isActive() ? 'نشط' : 'غير نشط' }}</span>@if($assignment->isActive())<form method="POST" action="{{ route('business.branches.assignments.destroy', [$branch, $assignment->user]) }}">@csrf @method('DELETE')<button class="text-xs font-bold text-red-600">تعطيل</button></form>@else<form method="POST" action="{{ route('business.branches.assignments.update', [$branch, $assignment->user]) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="active"><button class="text-xs font-bold text-indigo-600">تفعيل</button></form>@endif</div></div>@empty<p class="py-4 text-sm text-slate-500">لا توجد تعيينات لهذا الفرع.</p>@endforelse</div>
            </div>
            @if($branch->isActive() && ($branchesCount ?? 2) > 1)<form method="POST" action="{{ route('business.branches.deactivate', $branch) }}" class="mt-6">@csrf<button class="rounded-lg border border-red-200 px-4 py-2 text-sm font-bold text-red-600">تعطيل الفرع</button></form>@endif
        @endif
    </div>
@endsection
