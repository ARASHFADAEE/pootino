@extends('layouts.app')
@section('title', 'ویرایش آگهی')
@section('content')
<section class="mx-auto w-full max-w-3xl px-4 py-6 sm:px-6 lg:px-8">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-7">
        <h1 class="mb-5 text-lg font-extrabold">ویرایش آگهی</h1>
        @include('ads.form')
    </div>
</section>
@endsection
