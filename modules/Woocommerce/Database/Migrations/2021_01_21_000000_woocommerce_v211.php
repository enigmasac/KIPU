<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class WoocommerceV211 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('woocommerce_integrations', function (Blueprint $table) {
            $table->mediumText('akaunting_request')->change();
            $table->mediumText('woocommerce_response')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('woocommerce_integrations', function (Blueprint $table) {
            $table->text('akaunting_request')->change();
            $table->text('woocommerce_response')->change();
        });
    }
}
