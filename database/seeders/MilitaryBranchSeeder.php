<?php

namespace Database\Seeders;

use App\Models\MilitaryBranch;
use Illuminate\Database\Seeder;

class MilitaryBranchSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            ['type' => 'army', 'name' => 'ارتش جمهوری اسلامی ایران'],
            ['type' => 'sepah', 'name' => 'سپاه پاسداران انقلاب اسلامی'],
            ['type' => 'police', 'name' => 'نیروی انتظامی'],
        ];

        foreach ($branches as $branch) {
            MilitaryBranch::firstOrCreate(
                ['type' => $branch['type']],
                ['name' => $branch['name']],
            );
        }
    }
}
