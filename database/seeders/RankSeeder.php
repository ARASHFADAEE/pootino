<?php

namespace Database\Seeders;

use App\Models\Rank;
use Illuminate\Database\Seeder;

class RankSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([['name' => 'سرباز دوم', 'order' => 1], ['name' => 'سرباز اول', 'order' => 2], ['name' => 'سرجوخه', 'order' => 3]] as $rank) {
            Rank::create($rank);
        }
    }
}
