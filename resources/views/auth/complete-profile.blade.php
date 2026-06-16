@extends('layouts.auth')
@section('title', 'تکمیل ثبت‌نام | پوتینو')
@section('content')
<div class="flex min-h-screen items-center justify-center bg-[var(--color-accent-100)] px-4 py-8">
    <div class="w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <img src="{{ asset('img/Untitled.png') }}" alt="پوتینو" class="mx-auto mb-4 h-12 w-auto" />
        <h1 class="mb-1 text-center text-xl font-extrabold">تکمیل اطلاعات</h1>
        <p class="mb-6 text-center text-sm text-slate-500">برای ادامه، نام و کد ملی را وارد کن.</p>

        <form method="POST" action="{{ route('auth.otp.complete-profile') }}" class="space-y-4">
            @csrf
            <div>
                <label class="mb-1 block text-sm">نام و نام خانوادگی</label>
                <input name="name" value="{{ old('name', auth()->user()->name !== 'کاربر' ? auth()->user()->name : '') }}" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-[var(--color-primary-700)] focus:ring-[var(--color-primary-700)]" placeholder="نام و نام خانوادگی" />
            </div>
            <div>
                <label class="mb-1 block text-sm">کد ملی</label>
                <input dir="ltr" name="national_code" maxlength="10" value="{{ old('national_code') }}" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-center text-sm text-slate-900 placeholder:text-slate-400 focus:border-[var(--color-primary-700)] focus:ring-[var(--color-primary-700)]" placeholder="0012345678" />
            </div>
            <button class="w-full rounded-xl bg-[var(--color-primary-700)] py-3 text-sm font-semibold text-white">تکمیل ثبت‌نام</button>

            @if($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 p-3 text-xs text-red-700">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif
        </form>
    </div>
</div>
@endsection
