<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Province;
use Illuminate\Database\Seeder;

class ProvinceAndCitySeeder extends Seeder
{
    public function run(): void
    {
        $provincesPath = base_path('node_modules/iran-city/dist/list-of-cities-in-Iran/json/provinces.json');
        $citiesPath = base_path('node_modules/iran-city/dist/list-of-cities-in-Iran/json/cities.json');

        if (! file_exists($provincesPath) || ! file_exists($citiesPath)) {
            $this->seedFallback();
            return;
        }

        $provinces = json_decode((string) file_get_contents($provincesPath), true) ?: [];
        $cities = json_decode((string) file_get_contents($citiesPath), true) ?: [];

        $provinceMap = [];
        foreach ($provinces as $province) {
            $created = Province::firstOrCreate(['name' => $province['name']]);
            $provinceMap[(string) $province['id']] = $created->id;
        }

        foreach ($cities as $city) {
            $provinceId = $provinceMap[(string) $city['province_id']] ?? null;
            if (! $provinceId) {
                continue;
            }

            City::firstOrCreate([
                'province_id' => $provinceId,
                'name' => $city['name'],
            ]);
        }
    }

    private function seedFallback(): void
    {
        $data = [
            'تهران' => ['تهران', 'ری', 'اسلامشهر', 'ورامین', 'دماوند'],
            'اصفهان' => ['اصفهان', 'کاشان', 'نجف آباد', 'خمینی شهر', 'شاهین شهر'],
            'فارس' => ['شیراز', 'مرودشت', 'فسا', 'جهرم', 'لار'],
        ];

        foreach ($data as $provinceName => $cities) {
            $province = Province::firstOrCreate(['name' => $provinceName]);
            foreach ($cities as $city) {
                City::firstOrCreate(['province_id' => $province->id, 'name' => $city]);
            }
        }
    }
}
