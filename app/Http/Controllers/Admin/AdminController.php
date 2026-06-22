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
        $status = $request->string('status')->toString();

        $ads = Ad::query()
            ->with(['user', 'currentProvince', 'desiredProvince'])
            ->withCount('reports')
            ->when($status, fn ($q) => $q->where('status', $status))
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
        $ad->update([
            'status' => 'approved',
            'is_active' => true,
            'approved_at' => now(),
            'admin_note' => null,
        ]);

        return back()->with('success', 'آگهی تایید شد.');
    }

    public function reject(Request $request, Ad $ad): RedirectResponse
    {
        $ad->update([
            'status' => 'rejected',
            'is_active' => false,
            'admin_note' => $request->string('admin_note')->toString() ?: 'رد توسط مدیر',
        ]);

        return back()->with('success', 'آگهی رد شد.');
    }
}
