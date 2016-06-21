<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrganizationAdminsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('organization_admins', function (Blueprint $table) {
            $table->integer('organization_id')->unsigned()->default(0);
            $table->integer('user_id')->unsigned()->default(0);
            $table->foreign('organization_id')->references('id')->on('organizations')
                ->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('organization_admins');
    }
}
