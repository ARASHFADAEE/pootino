<?php

namespace Tests\Feature;

use App\Jobs\ModerateAdJob;
use App\Models\Ad;
use App\Models\MilitaryBranch;
use App\Models\Province;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiModerationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\MilitaryBranchSeeder::class);
        config([
            'services.ai_kar.api_key' => 'test-ai-kar-key',
            'services.ai_kar.auto_approve_in_local' => false,
        ]);
    }

    public function test_valid_ad_is_auto_approved_by_ai(): void
    {
        Http::fake([
            'https://api.ai-kar.com/v1/chat/completions' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => json_encode([
                            'approved' => true,
                            'reason' => 'آگهی مرتبط با تبادل محل خدمت است.',
                        ], JSON_UNESCAPED_UNICODE),
                    ],
                ]],
            ]),
        ]);

        $user = $this->makeVerifiedUser();
        $ad = $this->makePendingAd($user, 'تبادل تهران به شیراز', 'دنبال تبادل محل خدمت هستم.');

        $this->actingAs($user)->post(route('ads.store'), $this->adPayload($user, [
            'title' => 'تبادل تهران به شیراز',
            'description' => 'دنبال تبادل محل خدمت هستم.',
        ]))->assertRedirect(route('ads.my'));

        $created = Ad::latest('id')->first();
        $this->assertSame('approved', $created->status);
        $this->assertNotNull($created->approved_at);
        $this->assertTrue(Ad::approved()->where('id', $created->id)->exists());
    }

    public function test_nonsense_ad_is_rejected_by_ai(): void
    {
        Http::fake([
            'https://api.ai-kar.com/v1/chat/completions' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => json_encode([
                            'approved' => false,
                            'reason' => 'متن آگهی نامرتبط و بی‌معنا است.',
                        ], JSON_UNESCAPED_UNICODE),
                    ],
                ]],
            ]),
        ]);

        $user = $this->makeVerifiedUser();

        $this->actingAs($user)->post(route('ads.store'), $this->adPayload($user, [
            'title' => 'asdf qwer zxcv',
            'description' => 'تست تست اسپم بی‌معنا ۱۲۳۴۵',
        ]))->assertRedirect(route('ads.my'));

        $created = Ad::latest('id')->first();
        $this->assertSame('rejected', $created->status);
        $this->assertStringContainsString('رد خودکار', $created->admin_note);
    }

    public function test_moderate_job_keeps_pending_when_ai_is_unavailable(): void
    {
        Http::fake([
            'https://api.ai-kar.com/v1/chat/completions' => Http::response('error', 500),
        ]);

        $user = $this->makeVerifiedUser();
        $ad = $this->makePendingAd($user, 'تبادل اصفهان', 'توضیح منطقی');

        ModerateAdJob::dispatchSync($ad);

        $ad->refresh();
        $this->assertSame('pending', $ad->status);
        $this->assertStringContainsString('بررسی دستی', $ad->admin_note);
    }

    public function test_service_tries_fallback_model_when_primary_is_not_found(): void
    {
        Http::fake([
            'https://api.ai-kar.com/v1/chat/completions' => Http::sequence()
                ->push(['error' => ['message' => "Model 'google/gemini-2.0-flash' not found."]], 404)
                ->push([
                    'choices' => [[
                        'message' => [
                            'content' => json_encode([
                                'approved' => true,
                                'reason' => 'تایید شد.',
                            ], JSON_UNESCAPED_UNICODE),
                        ],
                    ]],
                ]),
        ]);

        config([
            'services.ai_kar.model' => 'google/gemini-2.0-flash',
            'services.ai_kar.fallback_models' => ['google/gemini-2.5-flash'],
        ]);

        $user = $this->makeVerifiedUser();
        $ad = $this->makePendingAd($user, 'تبادل شیراز', 'توضیح منطقی');

        ModerateAdJob::dispatchSync($ad);

        $ad->refresh();
        $this->assertSame('approved', $ad->status);
    }

    private function makeVerifiedUser(): User
    {
        return User::create([
            'name' => 'کاربر تست',
            'phone' => '09130000020',
            'national_code' => '1273835743',
        ]);
    }

    private function makePendingAd(User $user, string $title, string $description): Ad
    {
        $province = Province::create(['name' => 'تهران']);
        $branch = MilitaryBranch::where('type', 'army')->first();

        return Ad::create([
            'user_id' => $user->id,
            'title' => $title,
            'description' => $description,
            'current_province_id' => $province->id,
            'current_city_id' => null,
            'current_branch_id' => $branch->id,
            'desired_province_id' => $province->id,
            'desired_city_id' => null,
            'phone' => $user->phone,
            'status' => 'pending',
        ]);
    }

    private function adPayload(User $user, array $overrides = []): array
    {
        $current = Province::first() ?? Province::create(['name' => 'تهران']);
        $desired = Province::where('id', '!=', $current->id)->first() ?? Province::create(['name' => 'فارس']);

        return array_merge([
            'title' => 'تبادل محل خدمت',
            'description' => 'توضیح آگهی',
            'current_province_id' => $current->id,
            'desired_province_id' => $desired->id,
            'branch_type' => 'army',
            'phone' => $user->phone,
        ], $overrides);
    }
}
