<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEmergencyAlert extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alert_feed', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->integer('owner_id')->unsigned()->default(0);
            $table->integer('organization_id')->unsigned()->default(0);
            $table->string('country');
            $table->string('city');
            $table->string('source_id');
            $table->text('enabled');
            $table->foreign('source_id')
                ->references('source_id')->on('alert_source');
            $table->foreign('organization_id')
                ->references('id')->on('organizations')
                ->onDelete('cascade');
            $table->foreign('owner_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
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
        Schema::drop('alert_feed');
    }
}
