<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePokemonTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pokemon', function (Blueprint $table) {
            $table->id(); // Pokémon ID (Primary Key)
            $table->string('name'); // Pokémon name
            $table->string('type1')->nullable(); // Primary type
            $table->string('type2')->nullable(); // Secondary type
            $table->text('abilities')->nullable(); // JSON-encoded abilities
            $table->string('sprite_url')->nullable(); // URL to the Pokémon sprite
            $table->timestamps(); // Created and updated timestamps

            // Add indexing
            $table->index('name'); // Index for name
            $table->index('type1'); // Index for type1
            $table->index('type2'); // Index for type2
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pokemon');
    }
}