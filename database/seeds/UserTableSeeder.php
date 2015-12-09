<?php
namespace RollCall\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use RollCall\Entities\Models\User;


class UserTableSeeder extends Seeder
{
	public function run() {
		$user = User::firstOrCreate([
			'username' => 'admin',
			'email' => 'team@ushahidi.com',
		]);

		$user->update([
			'password' => bcrypt('westgate'),
		]);
	}
}
