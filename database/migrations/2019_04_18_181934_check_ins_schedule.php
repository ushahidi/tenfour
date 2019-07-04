<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CheckInsSchedule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('check_ins', function (Blueprint $table) {
            $table->unsignedBigInteger('scheduled_check_in_id')->nullable();
            $table->foreign('scheduled_check_in_id')
                ->references('id')->on('scheduled_check_in')
                ->onDelete('cascade');
            $table->dateTimeTz('send_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('check_ins', function (Blueprint $table) {
            $table->dropColumn('scheduled_check_in_id');
            $table->dropColumn('send_at');
        });
    }
}
