@extends('tracker::layouts.master')

@section('content')
    <main class="container">
        <article class="card" style="margin-top:48px">
            <h1>تعذر تحميل لوحة المتابعة</h1>
            <p>بيانات المتابعة غير صالحة حالياً. يجب على وكيل الذكاء الاصطناعي إصلاح ملف tracker/tracker.json قبل إعادة المحاولة.</p>
        </article>
    </main>
@endsection
