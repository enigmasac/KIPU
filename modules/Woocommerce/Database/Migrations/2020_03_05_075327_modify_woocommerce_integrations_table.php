<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyWoocommerceIntegrationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('woocommerce_integrations', function (Blueprint $table) {
            $table->integer('company_id')->after('id')->nullable();
            $table->text('woocommerce_response')->after('item_type')->nullable();
            $table->text('akaunting_request')->after('item_type')->nullable();
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
            $table->dropColumn('woocommerce_response')->nullable();
        });

        Schema::table('woocommerce_integrations', function (Blueprint $table) {
            $table->dropColumn('akaunting_request')->nullable();
        });
    }
}
