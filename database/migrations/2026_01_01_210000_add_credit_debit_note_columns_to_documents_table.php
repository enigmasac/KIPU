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
        Schema::table('documents', function (Blueprint $table) {
            if (!Schema::hasColumn('documents', 'invoice_id')) {
                $table->unsignedInteger('invoice_id')->nullable()->after('parent_id');
            }

            if (!Schema::hasColumn('documents', 'bill_id')) {
                $table->unsignedInteger('bill_id')->nullable()->after('invoice_id');
            }

            if (!Schema::hasColumn('documents', 'credit_note_reason_code')) {
                $table->string('credit_note_reason_code')->nullable()->after('bill_id');
            }

            if (!Schema::hasColumn('documents', 'credit_customer_account')) {
                $table->boolean('credit_customer_account')->default(false)->after('credit_note_reason_code');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['invoice_id', 'bill_id', 'credit_note_reason_code', 'credit_customer_account']);
        });
    }
};
