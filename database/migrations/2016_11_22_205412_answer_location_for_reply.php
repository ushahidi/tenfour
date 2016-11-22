<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AnswerLocationForReply extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('replies', function (Blueprint $table) {
            $table->string('location_text')->nullable();
            $table->string('answer')->nullable();
        });
        DB::statement('ALTER TABLE replies ADD location_point POINT' );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('replies', function (Blueprint $table) {
            $table->dropColumn(['location_text', 'location_point', 'answer']);
        });
    }
}
