<?php
namespace RollCall\Seeders;

use Illuminate\Database\Seeder;
use RollCall\Models\Setting;
use RollCall\Models\Organization;

class SettingsTableSeeder extends Seeder
{
    public function run() {

        $organization = Organization::where('name', 'Ushahidi')
                      ->select('id')
                      ->firstOrFail();

        Setting::firstOrCreate([
          'organization_id' => $organization->id,
          'key' => 'organization_types',
          'values' => ["election","firstresponders","humanrights","humanitarian","internationaldevelopment","anticorruption"]
        ]);

        Setting::firstOrCreate([
          'organization_id' => $organization->id,
          'key' => 'channels',
          'values' => ["email" => true]
        ]);
    }
}
