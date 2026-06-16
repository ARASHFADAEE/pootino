<?php

namespace Database\Seeders;

use App\Models\MilitaryBranch;
use App\Models\MilitaryOrganization;
use Illuminate\Database\Seeder;

class MilitaryOrganizationSeeder extends Seeder
{
    public function run(): void
    {
        $data = ['ارتش جمهوری اسلامی ایران' => ['نیروی زمینی ارتش'], 'سپاه پاسداران انقلاب اسلامی' => ['نیروی زمینی سپاه']];

        foreach ($data as $orgName => $branches) {
            $org = MilitaryOrganization::create(['name' => $orgName]);
            foreach ($branches as $branch) {
                MilitaryBranch::create(['organization_id' => $org->id, 'name' => $branch]);
            }
        }
    }
}
