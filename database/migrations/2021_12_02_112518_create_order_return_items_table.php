<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderReturnItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_return_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('order_return_id');
            $table->bigInteger('order_item_id')->unsigned()->nullable();
            $table->bigInteger('seller_product_id')->unsigned()->nullable();
            $table->bigInteger('size_availability_id')->unsigned()->nullable();

            $table->decimal('price', 8, 2)->nullable();
            $table->integer('quantity')->unsigned();
            $table->bigInteger('status_code');
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
        Schema::dropIfExists('order_return_items');
    }
}
