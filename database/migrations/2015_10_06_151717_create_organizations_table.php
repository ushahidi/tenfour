<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOrganizationsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('organizations', function ($table) {
            $table->engine = 'InnoDB';

            $table->increments('id')->unsigned();
            $table->string('name', 255)->nullable()->default(null);
            $table->string('url')->nullable()->default(null);
            $table->integer('user_id')->unsigned()->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('organizations');
    }

}
