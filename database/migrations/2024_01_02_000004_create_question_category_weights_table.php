<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_category_weights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->integer('weight'); // -10 to +10
            $table->timestamps();

            // Ensure unique combination
            $table->unique(['question_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_category_weights');
    }
};
