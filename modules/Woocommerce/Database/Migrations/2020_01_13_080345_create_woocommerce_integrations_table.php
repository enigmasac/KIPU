<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWoocommerceIntegrationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('woocommerce_integrations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('woocommerce_id');
            $table->integer('item_id');
            $table->string('item_type');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['item_id', 'item_type']);
            $table->index(['woocommerce_id', 'item_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('woocommerce_integrations');
    }
}
