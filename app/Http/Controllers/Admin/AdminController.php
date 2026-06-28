<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\AdReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->has('status')
            ? $request->string('status')->toString()
            : 'pending';

        $ads = Ad::query()
            ->with(['user', 'currentProvince', 'desiredProvince'])
            ->withCount('reports')
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.index', compact('ads', 'status'));
    }

    public function reports()
    {
        $reports = AdReport::query()
            ->with(['ad', 'user'])
            ->latest()
            ->paginate(30);

        return view('admin.reports', compact('reports'));
    }

    public function approve(Ad $ad): RedirectResponse
    {
        if ($ad->status !== 'pending') {
            return redirect()
                ->route('admin.index', ['status' => 'pending'])
                ->with('error', 'این آگهی قبلاً بررسی شده است.');
        }

        $ad->update([
            'status' => 'approved',
            'is_active' => true,
            'approved_at' => now(),
            'expires_at' => now()->addDays(30),
            'admin_note' => null,
            'edited_after_approval' => false,
        ]);

        return redirect()
            ->route('admin.index', ['status' => 'pending'])
            ->with('success', 'آگهی تایید شد و در لیست عمومی نمایش داده می‌شود.');
    }

    public function reject(Request $request, Ad $ad): RedirectResponse
    {
        if ($ad->status !== 'pending') {
            return redirect()
                ->route('admin.index', ['status' => 'pending'])
                ->with('error', 'این آگهی قبلاً بررسی شده است.');
        }

        $ad->update([
            'status' => 'rejected',
            'is_active' => false,
            'admin_note' => $request->string('admin_note')->toString() ?: 'رد توسط مدیر',
        ]);

        return redirect()
            ->route('admin.index', ['status' => 'pending'])
            ->with('success', 'آگهی رد شد.');
    }
}
