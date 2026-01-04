<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->string('email_subject')->nullable()->after('email_body');
            $table->string('pdf_page2_title')->nullable()->after('report_title');
            $table->text('pdf_page2_text')->nullable()->after('pdf_page2_title');
        });
    }

    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn(['email_subject', 'pdf_page2_title', 'pdf_page2_text']);
        });
    }
};
