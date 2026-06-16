<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'پوتینو | تبادل محل خدمت')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=vazirmatn:400,500,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-slate-800">
<div x-data="{ menuOpen: false }" class="min-h-screen">
    <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur">
        <nav class="mx-auto flex h-16 w-full max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <a href="{{ route('ads.index') }}" class="text-lg font-extrabold text-[var(--color-military-700)]">پوتینو</a>
            <button class="rounded-lg border border-slate-200 p-2 md:hidden" @click="menuOpen = !menuOpen">☰</button>
            <div class="hidden items-center gap-3 md:flex">
                <a href="{{ route('ads.index') }}" class="text-sm text-slate-600 hover:text-slate-900">آگهی‌ها</a>
                @auth
                    <a href="{{ route('ads.create') }}" class="rounded-lg bg-[var(--color-military-700)] px-4 py-2 text-sm text-white hover:bg-[var(--color-military-900)]">ثبت آگهی</a>
                    <a href="{{ route('ads.my') }}" class="text-sm text-slate-600 hover:text-slate-900">آگهی‌های من</a>
                    <form action="{{ route('auth.otp.logout') }}" method="POST">@csrf <button class="text-sm text-slate-600 hover:text-slate-900">خروج</button></form>
                @else
                    <a href="{{ route('auth.otp.phone') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm">ورود / ثبت‌نام</a>
                @endauth
            </div>
        </nav>
        <div x-show="menuOpen" x-transition class="border-t border-slate-100 bg-white p-4 md:hidden space-y-3">
            <a href="{{ route('ads.index') }}" class="block">آگهی‌ها</a>
            @auth
                <a href="{{ route('ads.create') }}" class="block">ثبت آگهی</a>
                <a href="{{ route('ads.my') }}" class="block">آگهی‌های من</a>
            @else
                <a href="{{ route('auth.otp.phone') }}" class="block">ورود / ثبت‌نام</a>
            @endauth
        </div>
    </header>

    @if(session('success'))
        <div class="mx-auto mt-4 w-full max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
        </div>
    @endif

    <main>@yield('content')</main>
</div>
</body>
</html>
