<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VerificationCode extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('unverified_addresses', function (Blueprint $table) {
            $table->char('code', 6);
            $table->integer('code_attempts')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('unverified_addresses', function (Blueprint $table) {
            $table->dropColumn('code');
            $table->dropColumn('code_attempts');
        });
    }
}
