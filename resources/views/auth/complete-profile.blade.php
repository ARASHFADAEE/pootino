@extends('layouts.auth')
@section('title', 'تکمیل ثبت‌نام | پوتینو')
@section('content')
<div class="flex min-h-screen items-center justify-center bg-[var(--color-accent-100)] px-4 py-8">
    <div class="w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <img src="{{ asset('img/Untitled.png') }}" alt="پوتینو" class="mx-auto mb-4 h-12 w-auto" />
        <h1 class="mb-1 text-center text-xl font-extrabold">تکمیل اطلاعات</h1>
        <p class="mb-6 text-center text-sm text-slate-500">اطلاعات هویتی خود را وارد کن تا احراز هویت انجام شود.</p>

        @if($errors->has('identity'))
            <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm leading-7 text-rose-900" role="alert">
                <p class="font-bold">عدم تطابق اطلاعات هویتی</p>
                <p class="mt-1">{{ $errors->first('identity') }}</p>
            </div>
        @endif

        <form method="POST" action="{{ route('auth.otp.complete-profile') }}" class="space-y-4">
            @csrf
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">نام <span class="text-red-500">*</span></label>
                <input name="first_name" value="{{ old('first_name') }}" required class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-[var(--color-primary-700)] focus:ring-[var(--color-primary-700)] @error('first_name') border-red-400 @enderror" placeholder="مثال: حسین" />
                @error('first_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">نام خانوادگی <span class="text-red-500">*</span></label>
                <input name="family" value="{{ old('family') }}" required class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-[var(--color-primary-700)] focus:ring-[var(--color-primary-700)] @error('family') border-red-400 @enderror" placeholder="مثال: فدائی جزی" />
                @error('family')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">نام پدر <span class="text-red-500">*</span></label>
                <input name="father_name" value="{{ old('father_name') }}" required class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-[var(--color-primary-700)] focus:ring-[var(--color-primary-700)] @error('father_name') border-red-400 @enderror" placeholder="مثال: محمود" />
                @error('father_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">کد ملی <span class="text-red-500">*</span></label>
                <input dir="ltr" name="national_code" maxlength="10" value="{{ old('national_code') }}" required class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-center text-sm text-slate-900 placeholder:text-slate-400 focus:border-[var(--color-primary-700)] focus:ring-[var(--color-primary-700)] @error('national_code') border-red-400 @enderror" placeholder="0012345678" />
                @error('national_code')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <x-jalali-date-picker name="birth_date" :value="old('birth_date', '')" />

            <button type="submit" class="w-full rounded-xl bg-[var(--color-primary-700)] py-3 text-sm font-semibold text-white">تکمیل ثبت‌نام</button>
        </form>
    </div>
</div>
@endsection
