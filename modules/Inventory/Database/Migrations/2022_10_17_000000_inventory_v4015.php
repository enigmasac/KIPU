<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InventoryV4015 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventory_transfer_order_items', function (Blueprint $table) {
            $table->double('transfer_quantity', 7, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inventory_transfer_order_items', function (Blueprint $table) {
            $table->integer('transfer_quantity')->nullable()->change();
        });
    }
}
