<?php

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
                'username' => 'charlie',
                'name'     => 'Charlie Harding',
                'email'    => 'charlie@ushahidi.com'
            ],
            [
                'username' => 'jason',
                'name'     => 'Jason Mule',
                'email'    => 'jason@ushahidi.com' 
            ],
            [
                'username' => 'linda',
                'name'     => 'Linda Kamau',
                'email'    => 'linda@ushahidi.com'
            ]
        ];

        foreach ($members as $member) {
            $member = User::firstOrCreate([
                'email'    => $member['email'],
                'username' => $member['username'],
                'name'     => $member['name']
            ]);
        
            $ids[$member['id']] = ['role' => 'member'];

        }

        $organization = Organization::where('name', '=', 'Ushahidi')->get()->first();

        $organization->members()->sync($ids, false);

    }
}
