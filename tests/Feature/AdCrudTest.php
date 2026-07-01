<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\MilitaryBranch;
use App\Models\Province;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\MilitaryBranchSeeder::class);
    }

    public function test_verified_user_can_store_ad_without_unit_name_or_rank(): void
    {
        $user = User::create([
            'name' => 'کاربر تست',
            'phone' => '09130000010',
            'national_code' => '1273835743',
        ]);

        $current = Province::create(['name' => 'تهران']);
        $desired = Province::create(['name' => 'فارس']);

        $response = $this->actingAs($user)->post(route('ads.store'), [
            'title' => 'تبادل تهران به فارس',
            'description' => 'توضیح تست',
            'current_province_id' => $current->id,
            'desired_province_id' => $desired->id,
            'branch_type' => 'army',
            'phone' => $user->phone,
        ]);

        $response->assertRedirect(route('ads.my'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('ads', [
            'user_id' => $user->id,
            'title' => 'تبادل تهران به فارس',
            'current_province_id' => $current->id,
            'desired_province_id' => $desired->id,
            'rank_id' => null,
            'education_level_id' => null,
            'status' => 'pending',
        ]);

        $ad = Ad::first();
        $this->assertNotNull($ad->current_branch_id);
        $this->assertSame('army', $ad->currentBranch->type);
    }

    public function test_store_accepts_persian_digits_in_phone_field(): void
    {
        $user = User::create([
            'name' => 'کاربر تست',
            'phone' => '09130000013',
            'national_code' => '1273835743',
        ]);

        $current = Province::create(['name' => 'تهران']);
        $desired = Province::create(['name' => 'فارس']);

        $this->actingAs($user)->post(route('ads.store'), [
            'title' => 'تبادل با شماره فارسی',
            'description' => 'توضیح تست',
            'current_province_id' => $current->id,
            'desired_province_id' => $desired->id,
            'branch_type' => 'army',
            'phone' => '۰۹۱۳۰۰۰۰۰۱۳',
        ])->assertRedirect(route('ads.my'));

        $this->assertDatabaseHas('ads', [
            'user_id' => $user->id,
            'phone' => '09130000013',
        ]);
    }

    public function test_store_fails_with_validation_errors_when_required_fields_missing(): void
    {
        $user = User::create([
            'name' => 'کاربر تست',
            'phone' => '09130000011',
            'national_code' => '1273835743',
        ]);

        $response = $this->actingAs($user)->from(route('ads.create'))->post(route('ads.store'), [
            'title' => '',
            'phone' => $user->phone,
        ]);

        $response->assertRedirect(route('ads.create'));
        $response->assertSessionHasErrors(['title', 'current_province_id', 'desired_province_id', 'branch_type']);
        $this->assertDatabaseCount('ads', 0);
    }

    public function test_user_can_update_and_delete_own_ad(): void
    {
        $user = User::create([
            'name' => 'کاربر تست',
            'phone' => '09130000012',
            'national_code' => '1273835743',
        ]);

        $current = Province::create(['name' => 'اصفهان']);
        $desired = Province::create(['name' => 'مازندران']);
        $branch = MilitaryBranch::where('type', 'sepah')->first();

        $ad = Ad::create([
            'user_id' => $user->id,
            'title' => 'آگهی اولیه',
            'current_province_id' => $current->id,
            'current_city_id' => null,
            'current_branch_id' => $branch->id,
            'desired_province_id' => $desired->id,
            'desired_city_id' => null,
            'rank_id' => null,
            'education_level_id' => null,
            'phone' => $user->phone,
            'status' => 'approved',
            'approved_at' => now(),
            'is_active' => true,
        ]);

        $this->actingAs($user)->put(route('ads.update', $ad), [
            'title' => 'آگهی ویرایش‌شده',
            'description' => 'توضیح جدید',
            'current_province_id' => $current->id,
            'desired_province_id' => $desired->id,
            'branch_type' => 'police',
            'phone' => $user->phone,
        ])->assertRedirect(route('ads.my'));

        $ad->refresh();
        $this->assertSame('آگهی ویرایش‌شده', $ad->title);
        $this->assertSame('pending', $ad->status);
        $this->assertSame('police', $ad->currentBranch->type);

        $this->actingAs($user)->delete(route('ads.destroy', $ad))
            ->assertRedirect();

        $this->assertSoftDeleted('ads', ['id' => $ad->id]);
    }
}
