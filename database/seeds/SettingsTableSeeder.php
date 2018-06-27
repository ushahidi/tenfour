<?php
namespace TenFour\Seeders;

use Illuminate\Database\Seeder;
use TenFour\Models\Setting;
use TenFour\Models\Organization;

class SettingsTableSeeder extends Seeder
{
    public function run() {

        $organization = Organization::where('name', 'Ushahidi')
                      ->select('id')
                      ->firstOrFail();

        Setting::firstOrCreate([
          'organization_id' => $organization->id,
          'key' => 'organization_types'
        ])->update([
          'values' => ["election","firstresponders","humanrights","humanitarian","internationaldevelopment","anticorruption"]
        ]);

        Setting::firstOrCreate([
          'organization_id' => $organization->id,
          'key' => 'channels',
        ])->update([
          'values' => [
            "email" => ["enabled" => true],
            "app" => ["enabled" => true],
            "sms" => ["enabled" => true]
          ]
        ]);

        Setting::firstOrCreate([
          'organization_id' => $organization->id,
          'key' => 'location',
        ])->update([
          'values' => ["name" => "Nairobi, Kenya"]
        ]);
    }
}
