<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->string('stripe_payment_intent_id', 128);
            $table->bigInteger('payment_info_id')->unsigned()->nullable();
            $table->tinyInteger('status_id')->unsigned();

            $table->string('street', 128);
            $table->string('city', 64);
            $table->string('province', 32);
            $table->string('country', 32);
            $table->string('postal_code', 16);
            $table->string('phone', 16);
            $table->string('email', 128);
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreign('status_id')->references('id')->on('order_statuses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
