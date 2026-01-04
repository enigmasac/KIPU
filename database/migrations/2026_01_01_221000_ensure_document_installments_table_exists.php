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
        if (!Schema::hasTable('document_installments')) {
            Schema::create('document_installments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id');
                $table->unsignedBigInteger('document_id');
                $table->decimal('amount', 15, 4);
                $table->date('due_at');
                $table->timestamps();
                $table->softDeletes();

                $table->index(['company_id', 'document_id']);
            });
        } else {
            // If table exists, ensure soft deletes column exists (in case it was missed)
            if (!Schema::hasColumn('document_installments', 'deleted_at')) {
                Schema::table('document_installments', function (Blueprint $table) {
                    $table->softDeletes();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We don't drop the table here to be safe as this is a "fix" migration
    }
};
