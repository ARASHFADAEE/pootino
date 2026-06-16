<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Province;
use Illuminate\Database\Seeder;

class ProvinceAndCitySeeder extends Seeder
{
    public function run(): void
    {
        $data = ['تهران' => ['تهران', 'ری'], 'اصفهان' => ['اصفهان', 'کاشان']];
        foreach ($data as $provinceName => $cities) {
            $province = Province::create(['name' => $provinceName]);
            foreach ($cities as $city) {
                City::create(['province_id' => $province->id, 'name' => $city]);
            }
        }
    }
}
