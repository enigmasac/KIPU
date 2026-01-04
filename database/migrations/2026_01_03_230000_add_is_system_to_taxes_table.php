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
        if (Schema::hasColumn('taxes', 'is_system')) {
            return;
        }

        Schema::table('taxes', function (Blueprint $table) {
            $table->boolean('is_system')->default(false)->after('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('taxes', 'is_system')) {
            return;
        }

        Schema::table('taxes', function (Blueprint $table) {
            $table->dropColumn('is_system');
        });
    }
};
