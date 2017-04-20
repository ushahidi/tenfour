<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ReplyToken extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('roll_call_recipients', function (Blueprint $table) {
            $table->string('reply_token')->nullable()->default(null);
            $table->index('reply_token');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('roll_call_recipients', function (Blueprint $table) {
            $table->dropColumn('reply_token');
        });
    }
}
