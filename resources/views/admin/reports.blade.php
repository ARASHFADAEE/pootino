@extends('layouts.app')

@section('title', 'گزارش‌های آگهی')

@section('content')
<section class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-lg font-extrabold text-slate-900">گزارش‌های آگهی</h1>
        <a href="{{ route('admin.index') }}" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">بازگشت به آگهی‌ها</a>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th class="px-3 py-3 text-right">آگهی</th>
                    <th class="px-3 py-3 text-right">گزارش‌دهنده</th>
                    <th class="px-3 py-3 text-right">دلیل</th>
                    <th class="px-3 py-3 text-right">توضیحات</th>
                    <th class="px-3 py-3 text-right">IP</th>
                    <th class="px-3 py-3 text-right">تاریخ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $report)
                    <tr class="border-t border-slate-100 align-top">
                        <td class="px-3 py-3">
                            <a href="{{ route('ads.show', $report->ad) }}" class="font-semibold text-slate-900 hover:text-[var(--color-primary-700)]">
                                {{ $report->ad->title ?? '—' }}
                            </a>
                        </td>
                        <td class="px-3 py-3 text-slate-600">
                            @if($report->user)
                                <p>{{ $report->user->name }}</p>
                                <p dir="ltr">{{ $report->user->phone }}</p>
                            @else
                                <span class="text-slate-400">کاربر حذف‌شده</span>
                            @endif
                        </td>
                        <td class="px-3 py-3">
                            <span class="rounded-full bg-rose-50 px-2 py-1 text-xs text-rose-700">{{ $report->reasonLabel() }}</span>
                        </td>
                        <td class="px-3 py-3 text-slate-600">{{ $report->description ?: '—' }}</td>
                        <td class="px-3 py-3 text-slate-500" dir="ltr">{{ $report->ip }}</td>
                        <td class="px-3 py-3 text-slate-500">{{ verta($report->created_at)->format('Y/m/d H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-3 py-6 text-center text-slate-500">گزارشی ثبت نشده است.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $reports->links() }}</div>
</section>
@endsection
