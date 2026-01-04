<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->text('intro_text')->nullable()->after('description');
            $table->string('analysis_graph_text')->nullable()->after('intro_text');
            $table->text('analysis_report_text')->nullable()->after('analysis_graph_text');
        });
    }

    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn(['intro_text', 'analysis_graph_text', 'analysis_report_text']);
        });
    }
};
