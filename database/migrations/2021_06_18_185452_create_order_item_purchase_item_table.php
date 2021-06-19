<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderItemPurchaseItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_item_purchase_item', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('order_item_id')->unsigned();
            $table->bigInteger('purchase_item_id')->unsigned();
            $table->timestamps();
        });

        Schema::table('order_item_purchase_item', function (Blueprint $table) {
            $table->foreign('order_item_id')->references('id')->on('order_items');
            $table->foreign('purchase_item_id')->references('id')->on('purchase_items');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_item_purchase_item');
    }
}
