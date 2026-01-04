<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            if (! Schema::hasColumn('documents', 'debit_note_reason_code')) {
                $table->string('debit_note_reason_code')->nullable()->after('credit_note_reason_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            if (Schema::hasColumn('documents', 'debit_note_reason_code')) {
                $table->dropColumn('debit_note_reason_code');
            }
        });
    }
};
