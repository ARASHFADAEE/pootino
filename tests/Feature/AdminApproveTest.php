<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\MilitaryBranch;
use App\Models\Province;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminApproveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\MilitaryBranchSeeder::class);
        config(['services.admin.phones' => '09129999999']);
    }

    public function test_admin_can_approve_pending_ad_and_it_appears_in_public_list(): void
    {
        $admin = User::create([
            'name' => 'ادمین',
            'phone' => '09129999999',
            'national_code' => '1273835743',
        ]);

        $owner = User::create([
            'name' => 'کاربر',
            'phone' => '09128888888',
            'national_code' => '0013520849',
        ]);

        $current = Province::create(['name' => 'تهران']);
        $desired = Province::create(['name' => 'فارس']);
        $branch = MilitaryBranch::where('type', 'army')->first();

        $ad = Ad::create([
            'user_id' => $owner->id,
            'title' => 'آگهی منتظر تایید',
            'current_province_id' => $current->id,
            'current_city_id' => null,
            'current_branch_id' => $branch->id,
            'desired_province_id' => $desired->id,
            'desired_city_id' => null,
            'phone' => $owner->phone,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.ads.approve', $ad));

        $response->assertRedirect(route('admin.index', ['status' => 'pending']));
        $response->assertSessionHas('success');

        $ad->refresh();
        $this->assertSame('approved', $ad->status);
        $this->assertTrue($ad->is_active);
        $this->assertNotNull($ad->approved_at);
        $this->assertNotNull($ad->expires_at);

        $this->get(route('ads.index'))
            ->assertOk()
            ->assertSee('آگهی منتظر تایید');

        $this->assertTrue(Ad::approved()->where('id', $ad->id)->exists());
    }

    public function test_admin_index_defaults_to_pending_filter(): void
    {
        $admin = User::create([
            'name' => 'ادمین',
            'phone' => '09129999999',
            'national_code' => '1273835743',
        ]);

        $owner = User::create([
            'name' => 'کاربر',
            'phone' => '09128888888',
            'national_code' => '0013520849',
        ]);

        $province = Province::create(['name' => 'تهران']);
        $branch = MilitaryBranch::where('type', 'army')->first();

        Ad::create([
            'user_id' => $owner->id,
            'title' => 'فقط pending',
            'current_province_id' => $province->id,
            'current_city_id' => null,
            'current_branch_id' => $branch->id,
            'desired_province_id' => $province->id,
            'desired_city_id' => null,
            'phone' => $owner->phone,
            'status' => 'pending',
        ]);

        Ad::create([
            'user_id' => $owner->id,
            'title' => 'قبلاً تایید شده',
            'current_province_id' => $province->id,
            'current_city_id' => null,
            'current_branch_id' => $branch->id,
            'desired_province_id' => $province->id,
            'desired_city_id' => null,
            'phone' => $owner->phone,
            'status' => 'approved',
            'approved_at' => now(),
            'is_active' => true,
        ]);

        $this->actingAs($admin)->get(route('admin.index'))
            ->assertOk()
            ->assertSee('فقط pending')
            ->assertDontSee('قبلاً تایید شده');
    }

    public function test_infinite_scroll_returns_json_with_next_page(): void
    {
        $owner = User::create([
            'name' => 'کاربر',
            'phone' => '09128888888',
            'national_code' => '0013520849',
        ]);

        $province = Province::create(['name' => 'تهران']);
        $branch = MilitaryBranch::where('type', 'army')->first();

        for ($i = 1; $i <= 13; $i++) {
            Ad::create([
                'user_id' => $owner->id,
                'title' => "آگهی شماره {$i}",
                'current_province_id' => $province->id,
                'current_city_id' => null,
                'current_branch_id' => $branch->id,
                'desired_province_id' => $province->id,
                'desired_city_id' => null,
                'phone' => $owner->phone,
                'status' => 'approved',
                'approved_at' => now()->subMinutes($i),
                'is_active' => true,
            ]);
        }

        $response = $this->getJson(route('ads.index', ['infinite' => 1, 'page' => 2]));

        $response->assertOk()
            ->assertJsonStructure(['html', 'next_page_url', 'has_more'])
            ->assertJsonFragment(['has_more' => false]);
    }
}
