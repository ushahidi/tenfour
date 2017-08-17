<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionsTable extends Migration
{
    /**
     * Create the subscription table
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscriptions', function($table) {
            $table->increments('id');
            $table->string('subscription_id')->unique();
            $table->string('customer_id');
            $table->string('status');
            $table->string('plan_id');
            $table->integer('organization_id')->index()->unsigned();
            $table->foreign('organization_id')->references('id')->on('organizations')
                ->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->integer('last_four')->nullable();
            $table->integer('expiry_month')->nullable();
            $table->integer('expiry_year')->nullable();
            $table->string('card_type');
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('next_billing_at')->nullable();
            $table->timestamps();
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn('paid_until');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('subscriptions');

        Schema::table('organizations', function (Blueprint $table) {
            $table->timestamp('paid_until')->nullable();
        });
    }
}
