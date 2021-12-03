<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderReturnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_returns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('order_id')->nullable();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->string('ep_shipment_id', 128)->nullable();

            $table->bigInteger('status_code');

            $table->string('first_name', 128);
            $table->string('last_name', 128);

            $table->string('street', 128);
            $table->string('city', 64);
            $table->string('province', 32);
            $table->string('country', 32);
            $table->string('postal_code', 16);
            $table->string('email', 128);
            $table->string('phone', 16)->nullable();

            $table->string('notes', 512)->nullable();

            $table->decimal('refund_subtotal', 8, 2)->nullable();
            $table->decimal('charged_shipping_fee', 8, 2)->nullable();
            $table->decimal('charged_other_fee', 8, 2)->nullable();
            $table->decimal('charged_tax', 8, 2)->nullable();

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
        Schema::dropIfExists('order_returns');
    }
}
