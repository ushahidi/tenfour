<?php
namespace RollCall\Seeders;

use Illuminate\Database\Seeder;
use RollCall\Models\User;
use RollCall\Models\Organization;
use RollCall\Models\Contact;
use Illuminate\Support\Facades\Hash;

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
            ],
            [
                'name'     => 'Team ushahidi',
                'email'    => 'team@ushahidi.com'
            ]
        ];

        $organization = Organization::where('name', '=', 'Ushahidi')->get()->first();

        foreach ($members as $member) {
        //while($i < $num_users) {
            $user = User::firstOrCreate([
                'name'     =>  $member['name'],
                'organization_id' => $organization->id
            ]);

            Contact::firstOrCreate([
                'type'              => 'email',
                'contact'           => $member['email'],
                'preferred'         => 1,
                'user_id'           => $user->id,
                'unsubscribe_token' => Hash::Make(config('app.key'))
            ]);

            $ids[$user['id']] = ['role' => 'member'];

            $user->update([
                'password' => 'westgate',
                'person_type' => 'user'
            ]);
        }

        $i = 0;
        $num_users = 100;
        //foreach ($members as $member) {
        while($i < $num_users) {
            $user = User::firstOrCreate([
                'name'     => 'Test User ' + $i,
                'organization_id' => $organization->id
            ]);

            Contact::firstOrCreate([
                'type'              => 'email',
                'contact'           => 'test_email_' + $i + '@ushahiditest.com',
                'preferred'         => 1,
                'user_id'           => $user->id,
                'unsubscribe_token' => Hash::Make(config('app.key'))
            ]);

            $ids[$user['id']] = ['role' => 'member'];

            $user->update([
                'password' => 'westgate',
                'person_type' => 'user'
            ]);
            $i++;
        }

    }
}
