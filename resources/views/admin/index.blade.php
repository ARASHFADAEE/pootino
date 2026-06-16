@extends('layouts.app')

@section('title', 'پنل ادمین')

@section('content')
<section class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-lg font-extrabold text-slate-900">مدیریت آگهی‌ها</h1>
        <form method="GET" class="flex items-center gap-2">
            <select name="status" onchange="this.form.submit()" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm">
                <option value="">همه وضعیت‌ها</option>
                <option value="pending" @selected($status === 'pending')>در انتظار تایید</option>
                <option value="approved" @selected($status === 'approved')>تایید شده</option>
                <option value="rejected" @selected($status === 'rejected')>رد شده</option>
            </select>
        </form>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th class="px-3 py-3 text-right">عنوان</th>
                    <th class="px-3 py-3 text-right">کاربر</th>
                    <th class="px-3 py-3 text-right">مسیر</th>
                    <th class="px-3 py-3 text-right">وضعیت</th>
                    <th class="px-3 py-3 text-right">عملیات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ads as $ad)
                    <tr class="border-t border-slate-100 align-top">
                        <td class="px-3 py-3 font-semibold text-slate-900">{{ $ad->title }}</td>
                        <td class="px-3 py-3 text-slate-600">
                            <p>{{ $ad->user->name ?? '—' }}</p>
                            <p dir="ltr">{{ $ad->user->phone ?? '—' }}</p>
                        </td>
                        <td class="px-3 py-3 text-slate-600">{{ $ad->currentCity->name ?? '-' }} → {{ $ad->desiredCity->name ?? '-' }}</td>
                        <td class="px-3 py-3">
                            <span class="rounded-full bg-slate-100 px-2 py-1 text-xs text-slate-700">{{ $ad->status }}</span>
                        </td>
                        <td class="px-3 py-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <a href="{{ route('ads.show', $ad) }}" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs">نمایش</a>
                                <form method="POST" action="{{ route('admin.ads.approve', $ad) }}">
                                    @csrf
                                    <button class="rounded-lg bg-green-700 px-3 py-1.5 text-xs text-white">تایید</button>
                                </form>
                                <form method="POST" action="{{ route('admin.ads.reject', $ad) }}" class="flex items-center gap-2">
                                    @csrf
                                    <input name="admin_note" class="rounded-lg border border-slate-300 px-2 py-1 text-xs" placeholder="دلیل رد (اختیاری)" />
                                    <button class="rounded-lg bg-rose-700 px-3 py-1.5 text-xs text-white">رد</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-3 py-6 text-center text-slate-500">آگهی‌ای پیدا نشد.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $ads->links() }}</div>
</section>
@endsection
