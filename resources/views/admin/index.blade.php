@extends('layouts.app')

@section('title', 'پنل ادمین')

@section('content')
<section class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-lg font-extrabold text-slate-900">مدیریت آگهی‌ها</h1>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.reports') }}" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">گزارش‌ها</a>
            <form method="GET" class="flex items-center gap-2">
                <select name="status" onchange="this.form.submit()" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm">
                    <option value="">همه وضعیت‌ها</option>
                    <option value="pending" @selected($status === 'pending')>در انتظار تایید</option>
                    <option value="approved" @selected($status === 'approved')>تایید شده</option>
                    <option value="rejected" @selected($status === 'rejected')>رد شده</option>
                </select>
            </form>
        </div>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th class="px-3 py-3 text-right">عنوان</th>
                    <th class="px-3 py-3 text-right">کاربر</th>
                    <th class="px-3 py-3 text-right">مسیر</th>
                    <th class="px-3 py-3 text-right">وضعیت</th>
                    <th class="px-3 py-3 text-right">گزارش‌ها</th>
                    <th class="px-3 py-3 text-right">عملیات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ads as $ad)
                    @php
                        $statusLabel = match($ad->status) {
                            'pending' => 'در انتظار تایید',
                            'approved' => 'تایید شده',
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
                    <tr class="border-t border-slate-100 align-top">
                        <td class="px-3 py-3 font-semibold text-slate-900">{{ $ad->title }}</td>
                        <td class="px-3 py-3 text-slate-600">
                            <p>{{ $ad->user->name ?? '—' }}</p>
                            <p dir="ltr">{{ $ad->user->phone ?? '—' }}</p>
                        </td>
                        <td class="px-3 py-3 text-slate-600">{{ $ad->currentProvince->name ?? '-' }} → {{ $ad->desiredProvince->name ?? '-' }}</td>
                        <td class="px-3 py-3">
                            <span class="rounded-full px-2 py-1 text-xs font-medium {{ $statusClass }}">{{ $statusLabel }}</span>
                        </td>
                        <td class="px-3 py-3">
                            @if($ad->reports_count > 0)
                                <span class="inline-flex min-w-6 items-center justify-center rounded-full bg-rose-600 px-2 py-0.5 text-xs font-semibold text-white">{{ $ad->reports_count }}</span>
                            @else
                                <span class="text-xs text-slate-400">۰</span>
                            @endif
                        </td>
                        <td class="px-3 py-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <a href="{{ route('ads.show', $ad) }}" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs">پیش‌نمایش</a>
                                @if($ad->status === 'pending')
                                    <form method="POST" action="{{ route('admin.ads.approve', $ad) }}">
                                        @csrf
                                        <button type="submit" class="rounded-lg bg-green-700 px-3 py-1.5 text-xs text-white">تایید</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.ads.reject', $ad) }}" class="flex items-center gap-2">
                                        @csrf
                                        <input name="admin_note" class="rounded-lg border border-slate-300 px-2 py-1 text-xs" placeholder="دلیل رد (اختیاری)" />
                                        <button type="submit" class="rounded-lg bg-rose-700 px-3 py-1.5 text-xs text-white">رد</button>
                                    </form>
                                @else
                                    <span class="text-xs text-slate-400">بررسی شده</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-3 py-8 text-center text-slate-500">
                            @if($status === 'pending')
                                آگهی در انتظار تاییدی وجود ندارد.
                            @else
                                آگهی‌ای پیدا نشد.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $ads->links() }}</div>
</section>
@endsection
