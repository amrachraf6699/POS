@extends('layouts.app')
@section('title', 'إضافة نسبة ضريبة')
@section('content')
<div class="mx-auto max-w-3xl space-y-6"><div><a href="{{ route('catalog.index') }}" class="text-sm font-bold text-indigo-600 hover:underline">الكتالوج والضرائب</a><h1 class="mt-4 text-3xl font-extrabold">إضافة نسبة ضريبة</h1></div>@if($errors->any())<div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert">يرجى مراجعة البيانات المدخلة.</div>@endif<form method="POST" action="{{ route('catalog.tax-rates.store') }}" class="grid gap-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:grid-cols-2 sm:p-7">@csrf @include('catalog::tax-rates.fields')<div class="sm:col-span-2"><button class="rounded-xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white">إنشاء نسبة الضريبة</button></div></form></div>
@endsection
