<?php
namespace RollCall\Seeders;

use Illuminate\Database\Seeder;
use RollCall\Models\User;
use RollCall\Models\Organization;

class OrgMemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {

        $ids = [];
        $members = [
           [
                'name'     => 'Charlie Test',
                'email'    => 'charlie+test@ushahidi.com'
            ],
            [
                'name'     => 'Jason Test',
                'email'    => 'jason+test@ushahidi.com'
            ],
            [
                'name'     => 'Linda Test',
                'email'    => 'linda+test@ushahidi.com'
            ],
            [
                'name'     => 'David Test',
                'email'    => 'dmcnamara+test@ushahidi.com'
            ]
        ];

        foreach ($members as $member) {
            $member = User::firstOrCreate([
                'email'    => $member['email'],
                'name'     => $member['name']
            ]);

            $ids[$member['id']] = ['role' => 'member'];

        }

        $organization = Organization::where('name', '=', 'Ushahidi')->get()->first();

        $organization->members()->sync($ids, false);

    }
}
