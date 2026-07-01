<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdRequest;
use App\Http\Requests\UpdateAdRequest;
use App\Jobs\ModerateAdJob;
use App\Jobs\SendAdToTelegramJob;
use App\Models\Ad;
use App\Models\MilitaryBranch;
use App\Models\Province;
use Illuminate\Database\Eloquent\Builder;
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
            $ads = $this->publicAdsQuery($filters)
                ->latest('approved_at')
                ->paginate(12)
                ->withQueryString();
            $totalActive = Ad::approved()->count();
        } catch (QueryException) {
            $ads = new LengthAwarePaginator([], 0, 12);
            $totalActive = 0;
        }

        if ($request->boolean('infinite')) {
            return response()->json([
                'html' => view('ads.partials.cards', compact('ads'))->render(),
                'next_page_url' => $ads->nextPageUrl(),
                'has_more' => $ads->hasMorePages(),
            ]);
        }

        if ($request->boolean('partial')) {
            return response()->json([
                'html' => view('ads.partials.results', compact('ads'))->render(),
            ]);
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
        $isPublic = $ad->status === 'approved' && $ad->is_active
            && ($ad->expires_at === null || $ad->expires_at->isFuture());

        if (! $isPublic) {
            $canPreview = Auth::check() && (
                Auth::id() === $ad->user_id
                || $this->isAdmin(Auth::user())
            );
            abort_unless($canPreview, 404);
        }

        if ($isPublic) {
            $ad->increment('views');
        }

        $ad->load([
            'user',
            'currentProvince', 'currentBranch',
            'desiredProvince',
        ]);

        $similarAds = $isPublic
            ? Ad::approved()
                ->where('id', '!=', $ad->id)
                ->where('desired_province_id', $ad->desired_province_id)
                ->with(['currentProvince', 'desiredProvince'])
                ->limit(3)
                ->get()
            : collect();

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

        $ad = $user->ads()->create($this->adAttributesFromValidated($validated, $user));

        ModerateAdJob::dispatchSync($ad);
        SendAdToTelegramJob::dispatch($ad);

        $message = $ad->fresh()->status === 'approved'
            ? 'آگهی شما تایید شد و منتشر شد.'
            : ($ad->fresh()->status === 'rejected'
                ? 'آگهی شما ثبت شد اما توسط سیستم بررسی رد شد. دلیل را در لیست آگهی‌های خود ببینید.'
                : 'آگهی شما ثبت شد و در صف بررسی قرار گرفت.');

        return redirect()->route('ads.my')->with('success', $message);
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

        $ad->update([
            ...$this->adAttributesFromValidated($request->validated(), $request->user()),
            'status' => 'pending',
            'approved_at' => null,
            'expires_at' => null,
            'edited_after_approval' => true,
        ]);

        SendAdToTelegramJob::dispatch($ad, true);
        ModerateAdJob::dispatchSync($ad);

        $ad->refresh();
        $message = match ($ad->status) {
            'approved' => 'آگهی ویرایش شد و دوباره منتشر شد.',
            'rejected' => 'آگهی ویرایش شد اما توسط سیستم بررسی رد شد.',
            default => 'آگهی ویرایش شد و در صف بررسی قرار گرفت.',
        };

        return redirect()->route('ads.my')->with('success', $message);
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
        $ads = $user->ads()->with(['currentProvince', 'desiredProvince', 'currentBranch'])->latest()->paginate(10);

        return view('ads.my-ads', compact('ads'));
    }

    private function publicAdsQuery(array $filters): Builder
    {
        return Ad::approved()
            ->with([
                'currentProvince', 'currentBranch',
                'desiredProvince',
            ])
            ->when($filters['current_province_id'] ?? null, fn ($q, $v) => $q->where('current_province_id', $v))
            ->when($filters['desired_province_id'] ?? null, fn ($q, $v) => $q->where('desired_province_id', $v))
            ->when($filters['branch_type'] ?? null, fn ($q, $v) => $q->whereHas('currentBranch', fn ($bq) => $bq->where('type', $v)))
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(fn ($sub) => $sub->where('title', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%"));
            });
    }

    private function formData(): array
    {
        return [
            'provinces' => Province::orderBy('name')->get(),
        ];
    }

    private function adAttributesFromValidated(array $validated, ?\App\Models\User $user = null): array
    {
        return [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'current_province_id' => $validated['current_province_id'],
            'desired_province_id' => $validated['desired_province_id'],
            'current_branch_id' => $this->resolveBranchId($validated['branch_type']),
            'current_city_id' => null,
            'desired_city_id' => null,
            'rank_id' => null,
            'education_level_id' => null,
            'phone' => $user?->phone ?? $validated['phone'],
        ];
    }

    private function resolveBranchId(string $type): int
    {
        $labels = [
            'army' => 'ارتش جمهوری اسلامی ایران',
            'sepah' => 'سپاه پاسداران انقلاب اسلامی',
            'police' => 'نیروی انتظامی',
        ];

        return MilitaryBranch::firstOrCreate(
            ['type' => $type],
            ['name' => $labels[$type] ?? $type],
        )->id;
    }

    private function isAdmin(?\App\Models\User $user): bool
    {
        if (! $user) {
            return false;
        }

        return collect(explode(',', (string) config('services.admin.phones', '')))
            ->map(fn ($phone) => trim($phone))
            ->filter()
            ->contains($user->phone);
    }
}
