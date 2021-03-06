<?php
namespace TenFour\Seeders;

use Illuminate\Database\Seeder;
use TenFour\Models\User;
use TenFour\Models\Organization;
use TenFour\Models\Contact;
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
                'email'    => 'team@ushahidi.com',
                'role'     => 'admin'
            ],
            [
                'name'     => 'Author Role',
                'email'    => 'author+test@ushahidi.com',
                'role'     => 'author'
            ],
            [
                'name'     => 'Viewer Role',
                'email'    => 'viewer+test@ushahidi.com',
                'role'     => 'viewer'
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
                'organization_id'   => $organization->id,
                'unsubscribe_token' => Hash::Make(config('app.key'))
            ]);

            $role = (isset($member['role'])?$member['role']:'responder');

            $ids[$user['id']] = ['role' => $role];

            $user->update([
                'password' => 'westgate',
                'person_type' => 'user',
                'role' => $role
            ]);
        }

        $i = 0;
        $num_users = 100;
        //foreach ($members as $member) {
        while($i < $num_users) {
            $user = User::firstOrCreate([
                'name'     => 'Zzest User ' . $i,
                'organization_id' => $organization->id
            ]);

            Contact::firstOrCreate([
                'type'              => 'email',
                'contact'           => 'test_email_' . $i . '@example.com',
                'preferred'         => 1,
                'user_id'           => $user->id,
                'organization_id'   => $organization->id,
                'unsubscribe_token' => Hash::Make(config('app.key'))
            ]);

            $ids[$user['id']] = ['role' => 'responder'];

            $user->update([
                'password' => 'westgate',
                'person_type' => 'user'
            ]);
            $i++;
        }

    }
}
