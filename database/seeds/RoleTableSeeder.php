<?php
namespace RollCall\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use RollCall\Entities\Models\Role;

class RoleTableSeeder extends Seeder
{
	public function run()
	{
		Role::firstOrCreate([
			'name' => 'admin',
			'description' => 'Administrative user, has access to everything'
		]);

		Role::firstOrCreate([
			'name' => 'member',
			'description' => 'Member user, has limited access',
		]);

		Role::firstOrCreate([
			'name' => 'login',
			'description' => 'Base login with no privileges',
		]);
	}
}
