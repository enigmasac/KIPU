<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sunat_certificates')) {
            return; // Table already exists
        }

        Schema::create('sunat_certificates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('name');
            $table->longText('content'); // Encrypted PEM content
            $table->string('password_encrypted')->nullable(); // For PFX files
            $table->date('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('thumbprint')->nullable();
            $table->string('issuer')->nullable();
            $table->string('subject')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('company_id');
            $table->index(['company_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sunat_certificates');
    }
};
