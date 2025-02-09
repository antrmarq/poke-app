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
            $table->id(); // Pokémon ID (Primary Key) (also international pokédex number)
            $table->string('name'); // Pokémon name
            $table->string('genus')->nullable(); // Pokémon genus
            $table->integer('generation_debut')->nullable(); // Generation debut
            $table->integer('height')->nullable();
            $table->integer('weight')->nullable();
            $table->boolean('is_baby')->nullable()->default(false);
            $table->boolean('is_legendary')->nullable()->default(false);
            $table->boolean('is_mythical')->nullable()->default(false);
            $table->boolean('is_fossil')->nullable()->default(false);
            $table->boolean('is_ultra_beast')->nullable()->default(false);
            $table->boolean('is_mega')->nullable()->default(false);
            $table->boolean('is_primal')->nullable()->default(false);
            $table->boolean('is_gmax')->nullable()->default(false);
            $table->string('color')->nullable();
            $table->string('shape')->nullable();
            $table->string('habitat')->nullable();
            $table->timestamps(); // Created and updated timestamps

            // Add indexing
            $table->index('name'); // Index for name
            $table->index('generation_debut'); // Index for generation_debut
            $table->index('is_legendary'); // Index for is_legendary
            $table->index('is_mythical'); // Index for is_mythical
            $table->index('is_fossil'); // Index for is_fossil
            $table->index('is_ultra_beast'); // Index for is_ultra_beast    
            $table->index('is_mega'); // Index for is_mega
            $table->index('is_primal'); // Index for is_primal
            $table->index('is_gmax'); // Index for is_gmax
            $table->index('color'); // Index for color
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
