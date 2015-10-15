<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('settings', function($table)
		{
			$table->engine = 'InnoDB';

			$table->bigIncrements('id')->unsigned();

			$table->bigInteger('organization_id')->unsigned()->nullable()->default(null);

			$table->string('key', 100)->default('');
			$table->text('value')->nullable()->default(null);

			$table->timestamps();

			$table->unique(array('organization_id', 'key'));
		});

	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('settings');
	}

}
