<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->string('label'); // e.g. 'Gehört werden'
            $table->string('key');   // e.g. 'gehoert_werden' (for internal logic/charts)
            $table->text('description')->nullable(); // For the result text
            $table->text('impulse_text')->nullable(); // The "Impuls" text
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
