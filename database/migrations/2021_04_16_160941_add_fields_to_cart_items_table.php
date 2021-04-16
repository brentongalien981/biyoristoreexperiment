<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToCartItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->unsignedBigInteger('product_seller_id')->after('product_id');
            $table->unsignedBigInteger('size_availability_id')->after('product_seller_id');
        });

        Schema::table('cart_items', function (Blueprint $table) {

            $table->foreign('product_seller_id')->references('id')->on('product_seller');
            $table->foreign('size_availability_id')->references('id')->on('size_availabilities');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropColumn('product_seller_id');
            $table->dropColumn('size_availability_id');
        });
    }
}
