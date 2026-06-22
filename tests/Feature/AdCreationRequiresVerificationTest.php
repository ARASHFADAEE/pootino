<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdCreationRequiresVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_unverified_user_cannot_access_ad_creation_form(): void
    {
        $user = User::create([
            'name' => 'کاربر تست',
            'phone' => '09130000001',
            'national_code' => null,
        ]);

        $response = $this->actingAs($user)->get(route('ads.create'));

        $response->assertRedirect(route('auth.otp.verification-required'));
    }

    public function test_verified_user_can_access_ad_creation_form(): void
    {
        $user = User::create([
            'name' => 'کاربر تست',
            'phone' => '09130000002',
            'national_code' => '1273835743',
        ]);

        $response = $this->actingAs($user)->get(route('ads.create'));

        $response->assertOk();
    }
}
