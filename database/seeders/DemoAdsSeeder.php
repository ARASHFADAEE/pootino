<?php

namespace Database\Seeders;

use App\Models\Ad;
use App\Models\City;
use App\Models\EducationLevel;
use App\Models\MilitaryBranch;
use App\Models\Province;
use App\Models\Rank;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoAdsSeeder extends Seeder
{
    public function run(): void
    {
        if (Ad::count() >= 12) {
            return;
        }

        $users = collect([
            ['name' => 'علی محمدی', 'phone' => '09120000001', 'national_code' => '0013520849'],
            ['name' => 'رضا حسینی', 'phone' => '09120000002', 'national_code' => '0079058748'],
            ['name' => 'مجتبی رضایی', 'phone' => '09120000003', 'national_code' => '0421583665'],
        ])->map(fn ($u) => User::firstOrCreate(['phone' => $u['phone']], $u));

        $cities = City::inRandomOrder()->limit(20)->get();
        $branches = MilitaryBranch::inRandomOrder()->limit(5)->get();
        $ranks = Rank::query()->get();
        $educationLevels = EducationLevel::query()->get();

        if ($cities->count() < 2 || $branches->isEmpty() || $ranks->isEmpty() || $educationLevels->isEmpty()) {
            return;
        }

        $titles = [
            'تبادل از تهران به شیراز',
            'درخواست جابجایی به اصفهان',
            'آماده تبادل محل خدمت در جنوب کشور',
            'تبادل فوری در استان های مرکزی',
            'دنبال تبادل به شهر محل سکونت',
            'تبادل در یگان پشتیبانی',
            'انتقال از پادگان فعلی به مرکز استان',
            'تبادل با شرایط توافقی',
            'امکان جابجایی تا پایان ماه',
            'تبادل سرباز وظیفه با شرایط مشابه',
            'درخواست انتقال به شهر خانواده',
            'تبادل قابل مذاکره',
        ];

        foreach ($titles as $i => $title) {
            $from = $cities->random();
            $to = $cities->where('id', '!=', $from->id)->random();
            $user = $users[$i % $users->count()];

            Ad::create([
                'user_id' => $user->id,
                'title' => $title,
                'description' => 'آگهی دمو برای تست تجربه کاربری نسخه موبایل و دسکتاپ.',
                'current_province_id' => $from->province_id,
                'current_city_id' => $from->id,
                'current_branch_id' => $branches->random()->id,
                'desired_province_id' => $to->province_id,
                'desired_city_id' => $to->id,
                'rank_id' => $ranks->random()->id,
                'education_level_id' => $educationLevels->random()->id,
                'phone' => $user->phone,
                'status' => 'approved',
                'approved_at' => now()->subDays(rand(0, 20)),
                'expires_at' => now()->addDays(rand(10, 30)),
                'is_active' => true,
                'views' => rand(15, 480),
                'edited_after_approval' => false,
            ]);
        }
    }
}
