<?php
namespace RollCall\Seeders;

use Illuminate\Database\Seeder;
use RollCall\Models\User;
use RollCall\Models\Organization;
use RollCall\Models\Contact;

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
            $user = User::firstOrCreate([
                'name'     => $member['name']
            ]);

            Contact::firstOrCreate([
                'type'        => 'email',
                'contact'     => $member['email'],
                'can_receive' => 1,
                'user_id'     => $user->id
            ]);

            $ids[$user['id']] = ['role' => 'member'];

        }

        $organization = Organization::where('name', '=', 'Ushahidi')->get()->first();

        $organization->members()->sync($ids, false);

    }
}
