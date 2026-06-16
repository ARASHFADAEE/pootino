@extends('layouts.auth')
@section('title', 'تایید کد | پوتینو')
@section('content')
<div class="flex min-h-screen items-center justify-center bg-[var(--color-accent-100)] px-4 py-8" x-data="{ timer: 120 }" x-init="setInterval(() => timer > 0 ? timer-- : 0, 1000)">
    <div class="w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <img src="{{ asset('img/Untitled.png') }}" alt="پوتینو" class="mx-auto mb-4 h-12 w-auto" />
        <h1 class="mb-1 text-center text-xl font-extrabold">تایید شماره</h1>
        <p class="mb-6 text-center text-sm text-slate-500">کد ارسال‌شده را وارد کن.</p>
        @if(app()->environment('local') && session('otp_preview_code'))
            <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 p-3 text-center text-sm text-amber-800">
                کد OTP تست: <span dir="ltr" class="font-bold">{{ session('otp_preview_code') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('auth.otp.verify') }}" class="space-y-4">
            @csrf
            <input name="code" maxlength="6" dir="ltr" class="w-full rounded-xl border border-slate-300 bg-white py-3 text-center text-xl tracking-[0.4em] text-slate-900 focus:border-[var(--color-primary-700)] focus:ring-[var(--color-primary-700)]" placeholder="------" />
            <button class="w-full rounded-xl bg-[var(--color-primary-700)] py-3 text-sm font-semibold text-white">ورود</button>
        </form>

        <form method="POST" action="{{ route('auth.otp.resend') }}" class="mt-4 text-center">@csrf
            <button :disabled="timer > 0" class="text-sm text-[var(--color-primary-700)] disabled:text-slate-400">ارسال مجدد <span x-show="timer > 0">(<span x-text="timer"></span>)</span></button>
        </form>
    </div>
</div>
@endsection
