<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * Adds 'apply_scope' field to taxes table to distinguish between:
     * - 'line': Tax applies to each line item (affects unit price) - e.g., IGV, ISC
     * - 'document': Tax applies to the document total - e.g., Detracción, Retención
     * - 'fixed_unit': Fixed amount per unit - e.g., ICBPER (plastic bags tax)
     */
    public function up(): void
    {
        Schema::table('taxes', function (Blueprint $table) {
            $table->string('apply_scope', 20)->default('line')->after('type');
        });

        // Update existing taxes to have 'line' scope by default
        // This is already handled by the default value
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('taxes', function (Blueprint $table) {
            $table->dropColumn('apply_scope');
        });
    }
};
