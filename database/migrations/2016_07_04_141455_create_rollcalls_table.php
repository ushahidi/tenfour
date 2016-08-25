<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRollcallsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rollcalls', function (Blueprint $table) {
            $table->increments('id');
            $table->string('message', 255)->nullable()->default(null);
            $table->integer('contact_id')->default(0);
            $table->integer('organization_id')->default(0);
            $table->enum('status', array(
                'pending', 'received', 'expired', 'cancelled', 'failed'
            ))->default('pending');
            $table->integer('sent')->default(0);
            $table->timestamps();

            $table->index('contact_id');

            $table->index('organization_id');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('rollcalls');
    }
}
