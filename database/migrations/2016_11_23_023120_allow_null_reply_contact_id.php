<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AllowNullReplyContactId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('replies', function (Blueprint $table) {
            $table->dropForeign(['contact_id']);

            $table->integer('contact_id')->unsigned()->default(null)->nullable()->change();
            $table->foreign('contact_id')->references('id')->on('contacts')
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
        Schema::table('replies', function (Blueprint $table) {
            $table->dropForeign(['contact_id']);
            $table->integer('contact_id')->unsigned()->default(0)->change();
            $table->foreign('contact_id')->references('id')->on('contacts')
                ->onDelete('cascade');
        });
    }
}
