<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRolesUsersTable extends Migration
{

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('roles_users', function (Blueprint $table) {
			$table->bigInteger('user_id')->unsigned()->default(0);
			$table->bigInteger('role_id')->unsigned()->default(0);
			$table->primary(['user_id', 'role_id']);
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
