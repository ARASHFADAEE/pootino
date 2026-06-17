<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdRequest;
use App\Http\Requests\UpdateAdRequest;
use App\Jobs\SendAdToTelegramJob;
use App\Models\Ad;
use App\Models\City;
use App\Models\EducationLevel;
use App\Models\MilitaryBranch;
use App\Models\Province;
use App\Models\Rank;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class AdController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only([
            'current_province_id', 'current_city_id', 'desired_province_id',
            'desired_city_id', 'rank_id', 'education_level_id', 'search',
        ]);

        try {
            $ads = Ad::approved()
                ->with([
                    'currentProvince', 'currentCity', 'currentBranch',
                    'desiredProvince', 'desiredCity', 'rank', 'educationLevel',
                ])
                ->when($filters['current_province_id'] ?? null, fn ($q, $v) => $q->where('current_province_id', $v))
                ->when($filters['current_city_id'] ?? null, fn ($q, $v) => $q->where('current_city_id', $v))
                ->when($filters['desired_province_id'] ?? null, fn ($q, $v) => $q->where('desired_province_id', $v))
                ->when($filters['desired_city_id'] ?? null, fn ($q, $v) => $q->where('desired_city_id', $v))
                ->when($filters['rank_id'] ?? null, fn ($q, $v) => $q->where('rank_id', $v))
                ->when($filters['education_level_id'] ?? null, fn ($q, $v) => $q->where('education_level_id', $v))
                ->when($filters['search'] ?? null, function ($q, $search) {
                    $q->where(fn ($sub) => $sub->where('title', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%"));
                })
                ->latest('approved_at')
                ->paginate(12)
                ->withQueryString();
            $totalActive = Ad::query()->where('status', 'approved')->where('is_active', true)->count();
        } catch (QueryException) {
            $ads = new LengthAwarePaginator([], 0, 12);
            $totalActive = 0;
        }

        try {
            $provinces = Province::query()->get();
            $cities = City::query()->orderBy('name')->get();
            $ranks = Rank::query()->get();
            $educationLevels = EducationLevel::query()->get();
        } catch (QueryException) {
            $provinces = collect();
            $cities = collect();
            $ranks = collect();
            $educationLevels = collect();
        }

        return view('ads.index', [
            'ads' => $ads,
            'filters' => $filters,
            'provinces' => $provinces,
            'cities' => $cities,
            'ranks' => $ranks,
            'educationLevels' => $educationLevels,
            'totalActive' => $totalActive,
        ]);
    }

    public function show(Ad $ad)
    {
        abort_if($ad->status !== 'approved' || ! $ad->is_active, 404);

        $ad->increment('views');
        $ad->load([
            'user',
            'currentProvince', 'currentCity', 'currentBranch',
            'desiredProvince', 'desiredCity', 'rank', 'educationLevel',
        ]);

        $similarAds = Ad::approved()
            ->where('id', '!=', $ad->id)
            ->where('desired_province_id', $ad->desired_province_id)
            ->with(['currentCity', 'desiredCity', 'rank'])
            ->limit(3)
            ->get();

        return view('ads.show', compact('ad', 'similarAds'));
    }

    public function create()
    {
        return view('ads.create', $this->formData());
    }

    public function store(StoreAdRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $validated = $request->validated();
        $validated['current_branch_id'] = $this->createBranch($validated['branch_type'], $validated['unit_name']);
        unset($validated['branch_type']);

        $ad = $user->ads()->create($validated);
        SendAdToTelegramJob::dispatch($ad);

        return redirect()->route('ads.my')->with('success', 'آگهی شما ثبت شد و پس از تایید منتشر می‌شود.');
    }

    public function edit(Ad $ad)
    {
        abort_if($ad->user_id !== Auth::id(), 403);
        $ad->load('currentBranch');

        return view('ads.edit', array_merge($this->formData(), ['ad' => $ad]));
    }

    public function update(UpdateAdRequest $request, Ad $ad)
    {
        abort_if($ad->user_id !== Auth::id(), 403);

        $validated = $request->validated();
        $validated['current_branch_id'] = $this->createBranch($validated['branch_type'], $validated['unit_name']);
        unset($validated['branch_type']);

        $ad->update([
            ...$validated,
            'status' => 'pending',
            'approved_at' => null,
            'expires_at' => null,
            'edited_after_approval' => true,
        ]);

        SendAdToTelegramJob::dispatch($ad, true);

        return redirect()->route('ads.my')->with('success', 'آگهی ویرایش شد و منتظر تایید مدیر است.');
    }

    public function destroy(Ad $ad)
    {
        abort_if($ad->user_id !== Auth::id(), 403);
        $ad->delete();

        return back()->with('success', 'آگهی حذف شد.');
    }

    public function myAds()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $ads = $user->ads()->with(['currentCity', 'desiredCity', 'rank'])->latest()->paginate(10);

        return view('ads.my-ads', compact('ads'));
    }

    private function formData(): array
    {
        return [
            'provinces' => Province::orderBy('name')->get(),
            'cities' => City::orderBy('name')->get(),
            'ranks' => Rank::orderBy('order')->get(),
            'educationLevels' => EducationLevel::orderBy('order')->get(),
        ];
    }

    private function createBranch(string $type, string $name): int
    {
        return MilitaryBranch::create([
            'type' => $type,
            'name' => $name,
        ])->id;
    }
}
