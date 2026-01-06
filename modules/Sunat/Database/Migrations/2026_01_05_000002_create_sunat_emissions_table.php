<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sunat_emissions')) {
            return; // Table already exists
        }

        Schema::create('sunat_emissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('document_id');
            $table->string('document_type'); // invoice, credit_note, debit_note
            $table->string('document_number');
            $table->string('sunat_code')->nullable(); // Response code from SUNAT
            $table->text('sunat_message')->nullable(); // Response message
            $table->string('ticket')->nullable(); // Async ticket number
            $table->string('hash')->nullable(); // Document hash
            $table->string('xml_path')->nullable(); // Path to stored XML
            $table->string('cdr_path')->nullable(); // Path to stored CDR
            $table->string('status')->default('pending'); // pending, sent, accepted, rejected, observed, error
            $table->json('observations')->nullable(); // SUNAT observations
            $table->unsignedInteger('retry_count')->default(0);
            $table->timestamp('emitted_at')->nullable();
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamps();

            $table->index('company_id');
            $table->index('document_id');
            $table->index('status');
            $table->index(['company_id', 'status']);
            $table->unique(['document_id', 'document_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sunat_emissions');
    }
};
