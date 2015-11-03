<?php

class DatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
        //Organization::truncate();

		Eloquent::unguard();

		 $this->call('OrganizationTableSeeder');
	}

}
