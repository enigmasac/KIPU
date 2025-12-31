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
        Schema::table('contacts', function (Blueprint $table) {
            $table->string('default_sunat_document_type')->default('01')->after('document_type'); // 01 Factura, 03 Boleta
            $table->string('default_sunat_operation_type')->default('01')->after('default_sunat_document_type'); // 01 Normal, 02 Gratuita
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->string('sunat_document_type')->nullable()->after('type');
            $table->string('sunat_operation_type')->nullable()->after('sunat_document_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn(['default_sunat_document_type', 'default_sunat_operation_type']);
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['sunat_document_type', 'sunat_operation_type']);
        });
    }
};