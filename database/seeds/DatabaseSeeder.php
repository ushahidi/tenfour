<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use RollCall\Seeders\OauthTableSeeder;

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

         $this->call(OauthTableSeeder::class);

        Model::reguard();
    }
}
