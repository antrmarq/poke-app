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
        Schema::create('gen_7_pokemon', function (Blueprint $table) {
            $table->id(); // Generation Pokédex number
            $table->foreignId('pokemon_id')->constrained()->onDelete('cascade'); //foreign key to pokemon table (international pokédex number)
            $table->string('type1')->nullable(); // Primary type
            $table->string('type2')->nullable(); // Secondary type
            $table->integer('hp')->nullable();
            $table->integer('attack')->nullable();
            $table->integer('defense')->nullable();
            $table->integer('special_attack')->nullable();
            $table->integer('special_defense')->nullable();
            $table->integer('speed')->nullable();
            $table->integer('base_experience')->nullable();
            $table->integer('capture_rate')->nullable();
            $table->integer('base_happiness')->nullable();
            $table->integer('growth_rate')->nullable();
            $table->boolean('is_genderless')->nullable()->default(false);
            $table->integer('male_gender_ratio')->nullable();
            $table->integer('female_gender_ratio')->nullable();
            $table->boolean('has_regional')->nullable()->default(false);
            $table->integer('hatch_counter')->nullable();
            $table->text('abilities')->nullable(); // JSON-encoded abilities
            $table->string('sprite_url')->nullable(); // URL to the Pokémon sprite (Json)
            $table->timestamps();

            // Indexing
            $table->index('pokemon_id'); // Index for pokemon_id
            $table->index('type1'); // Index for type1
            $table->index('type2'); // Index for type2
            $table->index('has_regional'); // Index for has_regional
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gen_7_pokemon');
    }
};
