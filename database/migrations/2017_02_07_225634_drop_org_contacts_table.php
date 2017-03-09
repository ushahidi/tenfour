<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropOrgContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('organization_contacts');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('organization_contacts', function (Blueprint $table) {
            $table->integer('organization_id')->unsigned()->default(0);
            $table->integer('contact_id')->unsigned()->default(0);
            $table->foreign('organization_id')->references('id')->on('organizations')
                ->onDelete('cascade');
            $table->foreign('contact_id')->references('id')->on('contacts')
                ->onDelete('cascade');
        });
    }
}
