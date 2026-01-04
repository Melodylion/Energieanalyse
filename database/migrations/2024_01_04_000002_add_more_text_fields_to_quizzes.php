<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->string('analysis_title')->nullable()->after('intro_text'); // 1. Header
            $table->text('analysis_text')->nullable()->after('analysis_title'); // 2. Sub-text
            $table->string('report_title')->nullable()->after('analysis_report_text'); // 4. Report Header
        });
    }

    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn(['analysis_title', 'analysis_text', 'report_title']);
        });
    }
};
