<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReturnItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('return_items', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('return_id')->unsigned(); 
            $table->bigInteger('order_item_id')->unsigned()->nullable();
            $table->integer('quantity');
            $table->timestamps();
        });

        Schema::table('return_items', function (Blueprint $table) {
            $table->foreign('return_id')->references('id')->on('returns');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('return_items');
    }
}
