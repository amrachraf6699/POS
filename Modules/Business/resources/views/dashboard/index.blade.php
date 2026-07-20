@extends('layouts.app')

@section('title', 'لوحة التحكم')

@section('navigation')
    <div class="flex w-full items-center justify-between"><span class="text-lg font-bold text-slate-900">نظام نقاط البيع</span><span class="text-sm text-slate-500">{{ $dashboard->tenant->name }}</span></div>
@endsection

@section('content')
    <div class="space-y-8">
        <div><p class="text-sm font-semibold text-indigo-600">مساحة العمل</p><h1 class="mt-2 text-3xl font-bold">مرحباً بك في {{ $dashboard->tenant->name }}</h1><p class="mt-2 text-slate-500">هذه نظرة عملية على البيانات المتاحة حالياً في مساحة العمل.</p></div>

        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">الفروع المتاحة لك</p><p class="mt-3 text-3xl font-bold">{{ $dashboard->accessibleBranchCount }}</p></div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">التعيينات النشطة</p><p class="mt-3 text-3xl font-bold">{{ $dashboard->activeAssignmentCount }}</p></div>
            @if($dashboard->canManage)<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">الفروع النشطة</p><p class="mt-3 text-3xl font-bold">{{ $dashboard->activeBranchCount }}</p><p class="mt-1 text-xs text-slate-400">{{ $dashboard->inactiveBranchCount }} غير نشط</p></div><div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">دعوات معلقة</p><p class="mt-3 text-3xl font-bold">{{ $dashboard->pendingInvitationCount }}</p></div>@else<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">دورك الحالي</p><p class="mt-3 text-xl font-bold">{{ $dashboard->membership->role === 'owner' ? 'مالك' : 'عضو' }}</p></div><div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">حالة الإعدادات</p><p class="mt-3 text-xl font-bold">متاحة</p></div>@endif
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2"><div class="flex items-center justify-between"><div><h2 class="text-xl font-bold">إجراءات سريعة</h2><p class="mt-1 text-sm text-slate-500">انتقل مباشرة إلى المهام المتاحة لك.</p></div></div><div class="mt-5 grid gap-3 sm:grid-cols-2">@if($dashboard->canManage)<a href="{{ route('business.settings.edit') }}" class="rounded-xl border border-slate-200 p-4 transition hover:border-indigo-300 hover:bg-indigo-50"><p class="font-bold">إعدادات النشاط</p><p class="mt-1 text-sm text-slate-500">الهوية والضريبة والإيصالات</p></a><a href="{{ route('business.branches.index') }}" class="rounded-xl border border-slate-200 p-4 transition hover:border-indigo-300 hover:bg-indigo-50"><p class="font-bold">إدارة الفروع</p><p class="mt-1 text-sm text-slate-500">الفروع وتعيينات الفريق</p></a><a href="{{ route('tenant.invitations.index') }}" class="rounded-xl border border-slate-200 p-4 transition hover:border-indigo-300 hover:bg-indigo-50"><p class="font-bold">دعوة أعضاء</p><p class="mt-1 text-sm text-slate-500">إدارة دعوات المديرين</p></a>@else<a href="{{ route('business.branches.index') }}" class="rounded-xl border border-slate-200 p-4 transition hover:border-indigo-300 hover:bg-indigo-50"><p class="font-bold">الفروع المتاحة</p><p class="mt-1 text-sm text-slate-500">عرض مواقع العمل المسموح بها</p></a>@endif<a href="{{ route('tenant.selection') }}" class="rounded-xl border border-slate-200 p-4 transition hover:border-indigo-300 hover:bg-indigo-50"><p class="font-bold">تبديل مساحة العمل</p><p class="mt-1 text-sm text-slate-500">اختيار نشاط تجاري آخر</p></a></div></section>
            <section class="rounded-2xl border border-slate-200 bg-slate-900 p-6 text-white shadow-sm"><h2 class="text-xl font-bold">قريباً</h2><p class="mt-1 text-sm text-slate-300">هذه الوحدات ستظهر هنا عند تنفيذ مراحلها.</p><ul class="mt-5 space-y-3 text-sm text-slate-200"><li>المنتجات والكتالوج</li><li>المخزون والتحويلات</li><li>نقطة البيع والمدفوعات</li><li>التقارير والتحليلات</li><li>الاشتراكات والفوترة</li></ul></section>
        </div>
    </div>
@endsection
