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
        // Force check directly on the table name with prefix if needed, 
        // but Schema builder handles prefix.
        if (!Schema::hasColumn('documents', 'sale_type')) {
            Schema::table('documents', function (Blueprint $table) {
                $table->string('sale_type')->default('cash')->after('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('documents', 'sale_type')) {
            Schema::table('documents', function (Blueprint $table) {
                $table->dropColumn('sale_type');
            });
        }
    }
};
