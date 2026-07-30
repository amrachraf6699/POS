@extends('layouts.app')
@php($editing = $category->exists)
@section('title', $editing ? 'تعديل الفئة' : 'إضافة فئة')
@section('content')
    <div class="mx-auto max-w-3xl space-y-6">
        <div><a href="{{ route('catalog.index') }}" class="text-sm font-bold text-indigo-600 hover:underline">الكتالوج
                والضرائب</a>
            <h1 class="mt-4 text-3xl font-extrabold">{{ $editing ? 'تعديل الفئة' : 'إضافة فئة جديدة' }}</h1>
        </div>
        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert">يرجى مراجعة
                البيانات المدخلة.</div>
        @endif
        <form method="POST"
            action="{{ $editing ? route('catalog.categories.update', $category) : route('catalog.categories.store') }}"
            class="space-y-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
            @csrf @if ($editing)
                @method('PUT')
            @endif
            <div>
                <label for="category-name" class="text-sm font-bold">اسم الفئة</label><input id="category-name"
                    name="name" value="{{ old('name', $category->name) }}" required
                    class="mt-2 h-12 w-full rounded-xl border border-slate-300 px-4">
            </div>
            <div><label for="category-description" class="text-sm font-bold">وصف مختصر</label>
                <textarea id="category-description" name="description" rows="4"
                    class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3">{{ old('description', $category->description) }}</textarea>
            </div><button class="rounded-xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white">حفظ الفئة</button>
        </form>
        @if ($editing)
            <form method="POST" action="{{ route('catalog.categories.destroy', $category) }}"
                onsubmit="return confirm('هل تريد حذف هذه الفئة؟');">@csrf @method('DELETE')<button
                    class="rounded-xl border border-red-200 px-5 py-3 text-sm font-bold text-red-600">حذف الفئة</button>
            </form>
        @endif
    </div>
@endsection
