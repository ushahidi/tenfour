<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveCascadeScheduledCheckin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('check_ins', function (Blueprint $table) {
            $table->dropForeign('check_ins_scheduled_check_in_id_foreign');
            $table->foreign('scheduled_checkin_id')
                ->references('id')->on('scheduled_checkin')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
