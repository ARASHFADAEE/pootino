<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdReportRequest;
use App\Models\Ad;
use App\Models\AdReport;

class AdReportController extends Controller
{
    public function store(StoreAdReportRequest $request, Ad $ad)
    {
        $exists = AdReport::where('ad_id', $ad->id)->where('ip', $request->ip())->exists();
        if ($exists) {
            return back()->with('error', 'قبلاً این آگهی را گزارش داده‌اید.');
        }

        AdReport::create([
            ...$request->validated(),
            'ad_id' => $ad->id,
            'user_id' => auth()->id(),
            'ip' => $request->ip(),
        ]);

        return back()->with('success', 'گزارش شما ثبت شد. متشکریم.');
    }
}
