<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class OrganizationCredits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->timestamp('paid_until')->nullable();
        });

        Schema::create('credit_adjustments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('organization_id')->unsigned();
            $table->foreign('organization_id')->references('id')->on('organizations')
                ->onDelete('cascade');
            $table->integer('adjustment');
            $table->integer('balance');
            $table->enum('type', array(
                'init', 'topup', 'rollcall', 'expire', 'misc'
            ))->default('misc');
            $table->timestamps();
        });

        foreach (DB::table('organizations')->get() as $organization) {

            DB::table('organizations')->where('id', $organization->id)->update([
                'paid_until' => DB::raw('NOW()')
            ]);

            DB::table('credit_adjustments')->insert([
                'organization_id' => $organization->id,
                'adjustment' => 0,
                'balance' => 0,
                'created_at' => DB::raw('NOW()'),
                'updated_at' => DB::raw('NOW()')
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn('paid_until');
        });

        Schema::drop('credit_adjustments');
    }
}
