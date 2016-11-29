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
                'name'     => 'Charlie Harding',
                'email'    => 'charlie@ushahidi.com'
            ],
            [
                'name'     => 'Jason Mule',
                'email'    => 'jason@ushahidi.com'
            ],
            [
                'name'     => 'Linda Kamau',
                'email'    => 'linda@ushahidi.com'
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
