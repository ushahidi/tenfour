<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFeedEntry extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alert_feed_entry', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->unsignedBigInteger('feed_id');
            $table->string('title');
            $table->string('body');
            $table->text('country')->nullable();
            $table->text('state')->nullable();
            $table->longText('metadata')->default(null); // other options ?
            $table->foreign('feed_id')
                ->references('id')->on('alert_feed')
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
        Schema::drop('alert_feed_entry');
    }
}
