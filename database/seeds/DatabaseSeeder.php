<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use RollCall\Seeders\OauthTableSeeder;
use RollCall\Seeders\UserTableSeeder;
use RollCall\Seeders\RoleTableSeeder;
use RollCall\Seeders\SettingsTableSeeder;
use RollCall\Seeders\NotificationsTableSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call(RollCall\Seeders\OauthTableSeeder::class);
        $this->call(RollCall\Seeders\OrganizationTableSeeder::class);
        $this->call(RollCall\Seeders\OrgMemberSeeder::class);
        $this->call(RollCall\Seeders\RollCallTableSeeder::class);
        $this->call(RollCall\Seeders\SettingsTableSeeder::class);
        $this->call(RollCall\Seeders\NotificationsTableSeeder::class);

        Model::reguard();
    }
}
