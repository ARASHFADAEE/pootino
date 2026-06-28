<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdRequest;
use App\Http\Requests\UpdateAdRequest;
use App\Jobs\SendAdToTelegramJob;
use App\Models\Ad;
use App\Models\MilitaryBranch;
use App\Models\Province;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class AdController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only([
            'current_province_id', 'desired_province_id', 'branch_type', 'search',
        ]);

        try {
            $ads = Ad::approved()
                ->with([
                    'currentProvince', 'currentCity', 'currentBranch',
                    'desiredProvince', 'desiredCity', 'rank', 'educationLevel',
                ])
                ->when($filters['current_province_id'] ?? null, fn ($q, $v) => $q->where('current_province_id', $v))
                ->when($filters['desired_province_id'] ?? null, fn ($q, $v) => $q->where('desired_province_id', $v))
                ->when($filters['branch_type'] ?? null, fn ($q, $v) => $q->whereHas('currentBranch', fn ($bq) => $bq->where('type', $v)))
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
        } catch (QueryException) {
            $provinces = collect();
        }

        return view('ads.index', [
            'ads' => $ads,
            'filters' => $filters,
            'provinces' => $provinces,
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
        $validated['current_city_id'] = null;
        $validated['desired_city_id'] = null;
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
        $validated['current_city_id'] = null;
        $validated['desired_city_id'] = null;
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
        $ads = $user->ads()->with(['currentProvince', 'desiredProvince', 'rank'])->latest()->paginate(10);

        return view('ads.my-ads', compact('ads'));
    }

    private function formData(): array
    {
        return [
            'provinces' => Province::orderBy('name')->get(),
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
