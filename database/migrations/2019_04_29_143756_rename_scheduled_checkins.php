<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameScheduledCheckins extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('scheduled_check_in', 'scheduled_checkin');

        Schema::table('scheduled_checkin', function(Blueprint $table) {
            $table->dropForeign('scheduled_check_in_check_ins_id_foreign');
            $table->dropColumn('check_ins_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('scheduled_checkin', 'scheduled_check_in');

        Schema::table('scheduled_check_in', function(Blueprint $table) {
            $table->integer('check_ins_id')->unsigned()->default(0);
            $table->foreign('check_ins_id')
                ->references('id')->on('check_ins')
                ->onDelete('cascade');
        });
    }
}
