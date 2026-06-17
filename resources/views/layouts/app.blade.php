<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'پوتینو | تبادل محل خدمت، ساده و سریع')</title>
    <meta name="description" content="@yield('meta_description', 'پوتینو، پلتفرم ثبت و جستجوی آگهی تبادل محل خدمت سربازی در سراسر کشور. سریع، ساده و به‌روز.')">
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    <meta name="googlebot" content="index, follow">
    <meta name="language" content="fa-IR">
    <meta name="revisit-after" content="2 days">
    <meta name="author" content="پوتینو">
    <link rel="canonical" href="{{ url()->current() }}">

    <meta property="og:locale" content="fa_IR">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="پوتینو">
    <meta property="og:title" content="@yield('title', 'پوتینو | تبادل محل خدمت، ساده و سریع')">
    <meta property="og:description" content="@yield('meta_description', 'پوتینو، پلتفرم ثبت و جستجوی آگهی تبادل محل خدمت سربازی در سراسر کشور. سریع، ساده و به‌روز.')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('img/Untitled.png') }}">
    <meta property="og:image:alt" content="لوگوی پوتینو">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', 'پوتینو | تبادل محل خدمت، ساده و سریع')">
    <meta name="twitter:description" content="@yield('meta_description', 'پوتینو، پلتفرم ثبت و جستجوی آگهی تبادل محل خدمت سربازی در سراسر کشور. سریع، ساده و به‌روز.')">
    <meta name="twitter:image" content="{{ asset('img/Untitled.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('img/logo-pootino.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/logo-pootino.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-slate-800">
<div class="min-h-screen" x-data="{ menuOpen: false }">
    <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur">
        <nav class="mx-auto flex h-16 w-full max-w-7xl items-center justify-between gap-3 px-4 sm:px-6 lg:px-8">
            <a href="{{ route('ads.index') }}" class="shrink-0">
                <img src="{{ asset('img/Untitled.png') }}" alt="پوتینو" class="h-10 w-auto" />
            </a>
            @auth
                <div class="hidden items-center gap-3 md:flex md:flex-1 md:justify-center">
                    <a href="{{ route('ads.index') }}" class="text-sm text-slate-600 hover:text-slate-900">آگهی‌ها</a>
                    <a href="{{ route('ads.my') }}" class="text-sm text-slate-600 hover:text-slate-900">آگهی‌های من</a>
                </div>
            @endauth
            <div class="flex items-center gap-2">
                @auth
                    <a href="{{ route('ads.create') }}" class="rounded-lg bg-[var(--color-primary-700)] px-4 py-2 text-xs font-semibold text-white hover:bg-[var(--color-primary-900)] sm:text-sm">ثبت آگهی</a>
                    @if(collect(explode(',', (string) env('ADMIN_PHONES', '')))->map(fn ($phone) => trim($phone))->contains(auth()->user()->phone))
                        <a href="{{ route('admin.index') }}" class="hidden rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-700 sm:inline-block">پنل ادمین</a>
                    @endif
                    <form action="{{ route('auth.otp.logout') }}" method="POST" class="hidden md:block">
                        @csrf
                        <button class="rounded-lg border border-rose-200 px-3 py-2 text-sm text-rose-700 hover:bg-rose-50">خروج</button>
                    </form>
                @else
                    <a href="{{ route('auth.otp.phone') }}" class="rounded-lg bg-[var(--color-primary-700)] px-3 py-1.5 text-xs font-semibold text-white sm:px-4 sm:py-2 sm:text-sm">ورود / ثبت‌نام</a>
                @endauth
                @auth
                <button
                    type="button"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-300 bg-white text-slate-700 md:hidden"
                    @click="menuOpen = !menuOpen"
                    :aria-expanded="menuOpen.toString()"
                    aria-controls="mobile-menu"
                    aria-label="باز کردن منو"
                >
                    <svg x-show="!menuOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7h16M4 12h16M4 17h16" />
                    </svg>
                    <svg x-show="menuOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 6l12 12M18 6l-12 12" />
                    </svg>
                </button>
                @endauth
            </div>
        </nav>
        @auth
        <div
            id="mobile-menu"
            x-show="menuOpen"
            x-transition
            @click.outside="menuOpen = false"
            class="border-t border-slate-200 bg-white px-4 py-3 md:hidden"
        >
            <div class="space-y-2">
                <a href="{{ route('ads.index') }}" class="block rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">آگهی‌ها</a>
                <a href="{{ route('ads.my') }}" class="block rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">آگهی‌های من</a>
                <a href="{{ route('ads.create') }}" class="block rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">ثبت آگهی</a>
                @if(collect(explode(',', (string) env('ADMIN_PHONES', '')))->map(fn ($phone) => trim($phone))->contains(auth()->user()->phone))
                    <a href="{{ route('admin.index') }}" class="block rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">پنل ادمین</a>
                @endif
                <form action="{{ route('auth.otp.logout') }}" method="POST" class="pt-1">
                    @csrf
                    <button class="w-full rounded-lg bg-rose-50 px-3 py-2 text-right text-sm font-medium text-rose-700">خروج</button>
                </form>
            </div>
        </div>
        @endauth

    </header>

    @if(session('success'))
        <div class="mx-auto mt-4 w-full max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
        </div>
    @endif

    @if(session('error'))
        <div class="mx-auto mt-4 w-full max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ session('error') }}</div>
        </div>
    @endif

    <main>@yield('content')</main>
</div>
</body>
</html>
