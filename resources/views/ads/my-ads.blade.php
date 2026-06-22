@extends('layouts.app')
@section('title', 'آگهی‌های من')
@section('content')
<section class="mx-auto w-full max-w-5xl px-4 py-6 sm:px-6 lg:px-8">
    <h1 class="mb-4 text-lg font-extrabold">آگهی‌های من</h1>

    @if($ads->count())
        <div class="space-y-3">
            @foreach($ads as $ad)
                <article class="rounded-2xl border border-slate-200 bg-white p-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 class="font-bold">{{ $ad->title }}</h2>
                            <p class="text-xs text-slate-500">{{ $ad->currentProvince->name ?? '-' }} ← {{ $ad->desiredProvince->name ?? '-' }}</p>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('ads.edit', $ad) }}" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs">ویرایش</a>
                            <form action="{{ route('ads.destroy', $ad) }}" method="POST">@csrf @method('DELETE')
                                <button class="rounded-lg border border-red-200 px-3 py-1.5 text-xs text-red-600">حذف</button>
                            </form>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
        <div class="mt-5">{{ $ads->links() }}</div>
    @else
        <x-empty-state>هنوز آگهی ثبت نکرده‌ای.</x-empty-state>
    @endif
</section>
@endsection
