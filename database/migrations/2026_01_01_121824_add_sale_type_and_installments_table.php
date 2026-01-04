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
        // Añadir tipo de venta a documentos
        if (!Schema::hasColumn('documents', 'sale_type')) {
            Schema::table('documents', function (Blueprint $table) {
                $table->string('sale_type')->default('cash')->after('status'); // cash, credit
            });
        }

        // Crear tabla de cuotas
        if (!Schema::hasTable('document_installments')) {
            Schema::create('document_installments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id');
                $table->unsignedBigInteger('document_id');
                $table->decimal('amount', 15, 4);
                $table->date('due_at');
                $table->timestamps();

                // Usamos la tabla completa con el prefijo si es necesario o dejamos que Laravel lo maneje
                $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_installments');
        if (Schema::hasColumn('documents', 'sale_type')) {
            Schema::table('documents', function (Blueprint $table) {
                $table->dropColumn('sale_type');
            });
        }
    }
};