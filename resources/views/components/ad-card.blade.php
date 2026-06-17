<article class="relative flex h-full flex-col rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:shadow-md">
    <a href="{{ route('ads.show', $ad) }}" class="absolute inset-0 z-0" aria-label="مشاهده آگهی {{ $ad->title }}"></a>
    <div class="mb-3 flex items-center justify-between text-xs text-slate-500">
        <span class="rounded-full bg-green-50 px-2 py-1 text-green-700">فعال</span>
        @if($ad->approved_at)
            @php
                $diffDays = (int) $ad->approved_at->diffInDays(now());
                $dateLabel = match(true) {
                    $diffDays === 0  => 'امروز',
                    $diffDays === 1  => 'دیروز',
                    $diffDays === 2  => '۲ روز پیش',
                    $diffDays <= 6   => fa_num($diffDays) . ' روز پیش',
                    $diffDays <= 29  => fa_num((int) ceil($diffDays / 7)) . ' هفته پیش',
                    default          => \Morilog\Jalali\Jalalian::fromCarbon($ad->approved_at)->format('j F'),
                };
            @endphp
            <span class="text-xs text-gray-400">{{ $dateLabel }}</span>
        @else
            <span class="text-xs text-gray-400">-</span>
        @endif
    </div>

    <h3 class="mb-2 line-clamp-2 text-sm font-bold leading-6 text-slate-900">{{ $ad->title }}</h3>

    <div class="space-y-1 text-xs text-slate-600">
        <p>محل فعلی: <strong>{{ $ad->currentCity->name ?? '-' }}</strong></p>
        <p>مقصد: <strong>{{ $ad->desiredCity->name ?? '-' }}</strong></p>
        <p>درجه: <strong>{{ $ad->rank->name ?? '-' }}</strong></p>
    </div>

    <div class="relative z-10 mt-4 flex items-center justify-between border-t border-slate-100 pt-3 text-xs">
        <span class="text-slate-400">{{ $ad->views }} بازدید</span>
        <a href="{{ route('ads.show', $ad) }}" class="rounded-lg bg-[var(--color-primary-700)] px-3 py-1.5 text-white">مشاهده</a>
    </div>
</article>
