@extends('layouts.auth')
@section('title', 'تایید کد | پوتینو')
@section('content')
<div class="flex min-h-screen items-center justify-center bg-[var(--color-cream-100)] px-4 py-8" x-data="{ timer: 120 }" x-init="setInterval(() => timer > 0 ? timer-- : 0, 1000)">
    <div class="w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="mb-1 text-center text-xl font-extrabold">تایید شماره</h1>
        <p class="mb-6 text-center text-sm text-slate-500">کد ارسال‌شده را وارد کن.</p>

        <form method="POST" action="{{ route('auth.otp.verify') }}" class="space-y-4">
            @csrf
            <input name="code" maxlength="6" dir="ltr" class="w-full rounded-xl border-slate-300 py-3 text-center text-xl tracking-[0.4em]" placeholder="------" />
            <button class="w-full rounded-xl bg-[var(--color-military-700)] py-3 text-sm font-semibold text-white">ورود</button>
        </form>

        <form method="POST" action="{{ route('auth.otp.resend') }}" class="mt-4 text-center">@csrf
            <button :disabled="timer > 0" class="text-sm text-[var(--color-military-700)] disabled:text-slate-400">ارسال مجدد <span x-show="timer > 0">(<span x-text="timer"></span>)</span></button>
        </form>
    </div>
</div>
@endsection
