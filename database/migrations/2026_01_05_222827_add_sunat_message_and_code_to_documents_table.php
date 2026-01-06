<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->text('sunat_message')->nullable()->after('sunat_status');
            $table->string('sunat_code')->nullable()->after('sunat_message');
            $table->string('sunat_hash')->nullable()->after('sunat_code');
            $table->string('sunat_cdr')->nullable()->after('sunat_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['sunat_message', 'sunat_code', 'sunat_hash', 'sunat_cdr']);
        });
    }
};
