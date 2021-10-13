<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReturnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->uuid('order_id')->nullable();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->string('ep_shipment_id', 128)->nullable();
            $table->bigInteger('status_code');

            $table->string('first_name', 128)->nullable();
            $table->string('last_name', 128)->nullable() ;

            $table->string('street', 128)->nullable();
            $table->string('city', 64)->nullable();
            $table->string('province', 32)->nullable();
            $table->string('country', 32)->nullable();
            $table->string('postal_code', 16)->nullable();
            $table->string('phone', 16)->nullable();
            $table->string('email', 128)->nullable();

            $table->decimal('shipping_fee', 8, 2)->nullable();
            $table->decimal('processing_fee', 8, 2)->nullable();
            $table->decimal('other_fee', 8, 2)->nullable();
            $table->decimal('charged_tax', 8, 2)->nullable();
            $table->decimal('subtotal_refund', 8, 2)->nullable();

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
        Schema::dropIfExists('returns');
    }
}
