<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->string('sunat_unit_code')->nullable()->default('NIU')->after('sku');
            $table->string('sunat_tax_type')->nullable()->default('10')->after('sunat_unit_code'); // 10: Gravado - Operacion Onerosa
        });

        Schema::table('document_items', function (Blueprint $table) {
            $table->string('sunat_unit_code')->nullable()->default('NIU')->after('sku');
            $table->string('sunat_tax_type')->nullable()->default('10')->after('sunat_unit_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['sunat_unit_code', 'sunat_tax_type']);
        });

        Schema::table('document_items', function (Blueprint $table) {
             $table->dropColumn(['sunat_unit_code', 'sunat_tax_type']);
        });
    }
};