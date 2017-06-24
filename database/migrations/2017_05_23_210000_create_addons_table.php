<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAddonsTable extends Migration
{
    /**
     * Create the addons table
     *
     * @return void
     */
    public function up()
    {
        Schema::create('addons', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('subscription_id')->index()->unsigned();
            $table->foreign('subscription_id')->references('id')->on('subscriptions')
                ->onDelete('cascade');
            $table->string('name');
            $table->string('addon_id');
            $table->integer('quantity')->default(0);
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
        Schema::drop('addons');
    }
}
