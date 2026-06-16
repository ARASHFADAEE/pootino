<?php

namespace Database\Seeders;

use App\Models\EducationLevel;
use Illuminate\Database\Seeder;

class EducationLevelSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([['name' => 'دیپلم', 'order' => 1], ['name' => 'کارشناسی', 'order' => 2], ['name' => 'کارشناسی ارشد', 'order' => 3]] as $level) {
            EducationLevel::create($level);
        }
    }
}
