<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use TenFour\Seeders\OauthTableSeeder;
use TenFour\Seeders\UserTableSeeder;
use TenFour\Seeders\RoleTableSeeder;
use TenFour\Seeders\SettingsTableSeeder;
use TenFour\Seeders\NotificationsTableSeeder;
use TenFour\Seeders\GroupTableSeeder;

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

        $this->call(TenFour\Seeders\OauthTableSeeder::class);
        $this->call(TenFour\Seeders\OrganizationTableSeeder::class);
        $this->call(TenFour\Seeders\OrgMemberSeeder::class);
        $this->call(TenFour\Seeders\CheckInTableSeeder::class);
        $this->call(TenFour\Seeders\SettingsTableSeeder::class);
        $this->call(TenFour\Seeders\NotificationsTableSeeder::class);
        $this->call(TenFour\Seeders\GroupTableSeeder::class);

        Model::reguard();
    }
}
