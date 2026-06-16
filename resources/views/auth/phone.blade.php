@extends('layouts.auth')
@section('title', 'ورود | پوتینو')
@section('content')
<div class="flex min-h-screen items-center justify-center bg-[var(--color-cream-100)] px-4 py-8">
    <div class="w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="mb-1 text-center text-xl font-extrabold">ورود با کد تایید</h1>
        <p class="mb-6 text-center text-sm text-slate-500">شماره موبایل را وارد کن تا کد ارسال شود.</p>

        <form method="POST" action="{{ route('auth.otp.send') }}" class="space-y-4">
            @csrf
            <div>
                <label class="mb-1 block text-sm">نام (اختیاری)</label>
                <input name="name" value="{{ old('name') }}" class="w-full rounded-xl border-slate-300" placeholder="نام و نام خانوادگی" />
            </div>
            <div>
                <label class="mb-1 block text-sm">شماره موبایل</label>
                <input dir="ltr" name="phone" value="{{ old('phone') }}" class="w-full rounded-xl border-slate-300 text-center" placeholder="09123456789" />
            </div>
            <button class="w-full rounded-xl bg-[var(--color-military-700)] py-3 text-sm font-semibold text-white">ارسال کد تایید</button>
        </form>
    </div>
</div>
@endsection
