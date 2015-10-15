<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function($table)
		{
			$table->engine = 'InnoDB';

			$table->bigIncrements('id')->unsigned();
			$table->string('email', 127);
			$table->string('name', 255)->nullable()->default(null);
			$table->string('password', 255);
			$table->string('remember_token', 100)->nullable();
			$table->integer('logins')->unsigned()->default(0);
			$table->integer('last_login')->unsigned()->default(0);
			$table->tinyInteger('active')->default(1);

			$table->timestamps();

			$table->unique('email');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('users');
	}

}
