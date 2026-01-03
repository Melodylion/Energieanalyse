<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            // Nullable first to allow existing records, but ideally we seed/update them
            // For this project, we might just cascade delete old ones or default to 1?
            // Let's make it nullable for safety, then we can enforce logic in code.
            $table->foreignId('quiz_id')->nullable()->after('respondent_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->dropForeign(['quiz_id']);
            $table->dropColumn('quiz_id');
        });
    }
};
