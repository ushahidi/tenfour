<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRollCallsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roll_calls', function (Blueprint $table) {
            $table->increments('id');
            $table->string('message', 255);
            $table->integer('organization_id')->unsigned()->default(0);
            $table->enum('status', array(
                'pending', 'received', 'expired', 'cancelled', 'failed'
            ))->default('pending');
            $table->boolean('sent');
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')
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
        Schema::drop('roll_calls');
    }
}
