<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RankSeeder::class,
            EducationLevelSeeder::class,
            MilitaryOrganizationSeeder::class,
            ProvinceAndCitySeeder::class,
            DemoAdsSeeder::class,
        ]);
    }
}
