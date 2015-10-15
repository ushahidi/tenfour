<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('group_users', function($table)
		{
			$table->engine = 'InnoDB';

			$table->bigInteger('group_id')->unsigned()->default(0);
			$table->bigInteger('user_id')->unsigned();

			$table->primary(array('group_id', 'user_id'));

			$table->foreign('group_id')
				->references('id')->on('groups')
				->onDelete('cascade');

			$table->foreign('user_id')
				->references('id')->on('users')
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
		Schema::drop('group_users');
	}

}
