<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRolesUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('roles_users', function($table)
		{
			$table->engine = 'InnoDB';

			$table->bigInteger('user_id')->unsigned()->default(0);
			$table->bigInteger('role_id')->unsigned()->default(0);

			$table->primary(array('user_id', 'role_id'));

			$table->foreign('user_id')
				->references('id')->on('users')
				->onDelete('cascade');

			$table->foreign('role_id')
				->references('id')->on('roles')
				->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('roles_users');
	}

}
