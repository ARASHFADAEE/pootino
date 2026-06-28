@extends('layouts.app')
@section('title', 'آگهی‌های من')
@section('content')
<section class="mx-auto w-full max-w-5xl px-4 py-6 sm:px-6 lg:px-8">
    <h1 class="mb-4 text-lg font-extrabold">آگهی‌های من</h1>

    @if($ads->count())
        <div class="space-y-3">
            @foreach($ads as $ad)
                @php
                    $statusLabel = match($ad->status) {
                        'pending' => 'در حال بررسی',
                        'approved' => 'منتشر شده',
                        'rejected' => 'رد شده',
                        default => $ad->status,
                    };
                    $statusClass = match($ad->status) {
                        'pending' => 'bg-amber-100 text-amber-800',
                        'approved' => 'bg-green-100 text-green-800',
                        'rejected' => 'bg-rose-100 text-rose-800',
                        default => 'bg-slate-100 text-slate-700',
                    };
                @endphp
                <article class="rounded-2xl border border-slate-200 bg-white p-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <div class="mb-2 flex flex-wrap items-center gap-2">
                                <h2 class="font-bold text-slate-900">{{ $ad->title }}</h2>
                                <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $statusClass }}">{{ $statusLabel }}</span>
                            </div>
                            <p class="text-xs text-slate-500">{{ $ad->currentProvince->name ?? '-' }} ← {{ $ad->desiredProvince->name ?? '-' }}</p>
                            @if($ad->status === 'rejected' && $ad->admin_note)
                                <p class="mt-2 rounded-lg border border-rose-100 bg-rose-50 px-3 py-2 text-xs leading-6 text-rose-800">{{ $ad->admin_note }}</p>
                            @elseif($ad->status === 'pending' && $ad->admin_note)
                                <p class="mt-2 rounded-lg border border-amber-100 bg-amber-50 px-3 py-2 text-xs leading-6 text-amber-800">{{ $ad->admin_note }}</p>
                            @endif
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
