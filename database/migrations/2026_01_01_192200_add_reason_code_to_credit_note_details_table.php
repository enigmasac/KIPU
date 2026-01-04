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
        if (Schema::hasTable('credit_debit_notes_credit_note_details')) {
            Schema::table('credit_debit_notes_credit_note_details', function (Blueprint $table) {
                if (!Schema::hasColumn('credit_debit_notes_credit_note_details', 'reason_code')) {
                    $table->string('reason_code')->nullable()->after('invoice_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('credit_debit_notes_credit_note_details')) {
            Schema::table('credit_debit_notes_credit_note_details', function (Blueprint $table) {
                if (Schema::hasColumn('credit_debit_notes_credit_note_details', 'reason_code')) {
                    $table->dropColumn('reason_code');
                }
            });
        }
    }
};