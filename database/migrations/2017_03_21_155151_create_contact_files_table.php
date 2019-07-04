<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContactFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contact_files', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('organization_id')->unsigned()->default(0);
            $table->text('columns')->nullable();
            $table->text('maps_to')->nullable()->default(null);
            $table->integer('size')->unsigned()->default(0);
            $table->string('filename')->nullable();
            $table->string('mime', 255)->nullable();
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
        Schema::drop('contact_files');
    }
}
