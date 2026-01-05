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
        if (Schema::hasTable('currencies') && !Schema::hasColumn('currencies', 'sunat_rate')) {
            Schema::table('currencies', function (Blueprint $table) {
                $table->decimal('sunat_rate', 15, 4)->nullable()->after('rate');
            });
        }

        if (Schema::hasTable('email_templates') && !Schema::hasColumn('email_templates', 'group')) {
            Schema::table('email_templates', function (Blueprint $table) {
                $table->string('group')->nullable()->index()->after('alias');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('currencies') && Schema::hasColumn('currencies', 'sunat_rate')) {
            Schema::table('currencies', function (Blueprint $table) {
                $table->dropColumn('sunat_rate');
            });
        }

        if (Schema::hasTable('email_templates') && Schema::hasColumn('email_templates', 'group')) {
            Schema::table('email_templates', function (Blueprint $table) {
                $table->dropColumn('group');
            });
        }
    }
};