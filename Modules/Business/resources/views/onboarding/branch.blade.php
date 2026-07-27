@extends('layouts.app')
@section('title', 'إعداد الفرع الأول')
@section('content')
    <div class="mx-auto max-w-2xl space-y-6">
        <div>
            <p class="text-sm font-bold text-indigo-600">الخطوة ٢ من ٣</p>
            <h1 class="mt-2 text-3xl font-extrabold text-slate-900">أنشئ أول فرع نشط</h1>
            <p class="mt-2 text-sm text-slate-600">يمكنك تعديل بياناته وإضافة فروع أخرى لاحقاً.</p>
        </div>
        @if($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert">
                <ul class="list-disc pr-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>@endif
        <form method="POST" action="{{ route('business.onboarding.branch.save') }}"
            class="grid gap-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm md:grid-cols-2">@csrf
            <div><label class="text-sm font-bold">اسم الفرع</label><input name="name" value="{{ old('name') }}" required
                    class="mt-2 h-12 w-full rounded-xl border border-slate-300 px-4"></div>
            <div><label class="text-sm font-bold">رمز الفرع</label><input name="code" value="{{ old('code') }}" required
                    class="mt-2 h-12 w-full rounded-xl border border-slate-300 px-4"></div>
            <div><label class="text-sm font-bold">المدينة</label><input name="city" value="{{ old('city') }}"
                    class="mt-2 h-12 w-full rounded-xl border border-slate-300 px-4"></div>
            <div><label class="text-sm font-bold">الهاتف</label><input name="phone" value="{{ old('phone') }}"
                    class="mt-2 h-12 w-full rounded-xl border border-slate-300 px-4"></div>
            <input type="hidden" name="country_code" value="EG"><input type="hidden" name="timezone" value="Africa/Cairo">
            <div class="md:col-span-2"><button class="rounded-xl bg-indigo-600 px-6 py-3 font-bold text-white">حفظ
                    ومتابعة</button></div>
        </form>
    </div>
@endsection