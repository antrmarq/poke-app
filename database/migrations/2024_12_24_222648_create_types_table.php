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
        Schema::create('types', function (Blueprint $table) {
            $table->id();
            $table->string('type')->unique();
            $table->string('no_effect_to')->nullable();
            $table->string('not_very_effective_to')->nullable();
            $table->string('super_effective_to')->nullable();
            $table->string('immune_to')->nullable();
            $table->string('resists_to')->nullable();
            $table->string('weak_to')->nullable();
            $table->enum('category', ['physical', 'special'])->default('physical')->nullable(); // gen 1-3

            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('types');
    }
};
