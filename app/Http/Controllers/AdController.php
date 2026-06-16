<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdRequest;
use App\Http\Requests\UpdateAdRequest;
use App\Jobs\SendAdToTelegramJob;
use App\Models\Ad;
use App\Models\EducationLevel;
use App\Models\MilitaryOrganization;
use App\Models\Province;
use App\Models\Rank;
use Illuminate\Http\Request;

class AdController extends Controller
{
    public function index(Request $request)
    {
        $ads = Ad::approved()->with(['currentCity', 'desiredCity', 'rank'])->latest()->paginate(12);
        return view('ads.index', [
            'ads' => $ads,
            'provinces' => Province::orderBy('name')->get(),
            'organizations' => MilitaryOrganization::with('branches')->get(),
            'ranks' => Rank::orderBy('order')->get(),
            'educationLevels' => EducationLevel::orderBy('order')->get(),
            'totalActive' => Ad::approved()->count(),
        ]);
    }

    public function show(Ad $ad)
    {
        abort_if($ad->status !== 'approved' || ! $ad->is_active, 404);
        $ad->increment('views');
        return view('ads.show', compact('ad'));
    }

    public function create() { return view('ads.create'); }

    public function store(StoreAdRequest $request)
    {
        $ad = auth()->user()->ads()->create($request->validated());
        SendAdToTelegramJob::dispatch($ad);
        return redirect()->route('ads.my')->with('success', 'آگهی ثبت شد.');
    }

    public function edit(Ad $ad)
    {
        abort_if($ad->user_id !== auth()->id(), 403);
        return view('ads.edit', compact('ad'));
    }

    public function update(UpdateAdRequest $request, Ad $ad)
    {
        abort_if($ad->user_id !== auth()->id(), 403);
        $ad->update([...$request->validated(), 'status' => 'pending']);
        SendAdToTelegramJob::dispatch($ad, true);
        return redirect()->route('ads.my')->with('success', 'آگهی ویرایش شد.');
    }

    public function destroy(Ad $ad)
    {
        abort_if($ad->user_id !== auth()->id(), 403);
        $ad->delete();
        return back()->with('success', 'آگهی حذف شد.');
    }

    public function myAds()
    {
        $ads = auth()->user()->ads()->latest()->paginate(10);
        return view('ads.my-ads', compact('ads'));
    }
}
