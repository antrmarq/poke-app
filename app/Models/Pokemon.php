<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pokemon extends Model
{
    use HasFactory;

    // Table name (optional, if Laravel doesn't recognize it automatically)
    protected $table = 'pokemon';

    // Attributes allowed for mass assignment
    protected $fillable = [
        'id',              // Pokémon ID
        'name',            // Pokémon name
        'genus',           // Pokémon genus
        'generation_debut', // Generation debut
        'height',          // Height in decimeters
        'weight',          // Weight in hectograms
        'is_baby',         // Boolean flag for baby Pokémon
        'is_legendary',    // Boolean flag for legendary Pokémon
        'is_mythical',     // Boolean flag for mythical Pokémon
        'is_fossil',       // Boolean flag for fossil Pokémon
        'is_ultra_beast',  // Boolean flag for ultra beasts
        'is_mega',         // Boolean flag for mega evolution
        'is_primal',       // Boolean flag for primal form
        'is_gmax',         // Boolean flag for Gigantamax form
        'color',           // Pokémon color
        'shape',           // Shape of the Pokémon
        'habitat',         // Habitat of the Pokémon        'abilities',        // JSON-encoded abilities
        'sprite_url',       // URL for the Pokémon sprite
    ];

    // Ensure correct data types
    protected $casts = [
        'id' => 'integer',
        'generation_debut' => 'integer',
        'height' => 'integer',
        'weight' => 'integer',
        'is_baby' => 'boolean',
        'is_legendary' => 'boolean',
        'is_mythical' => 'boolean',
        'is_fossil' => 'boolean',
        'is_ultra_beast' => 'boolean',
        'is_mega' => 'boolean',
        'is_primal' => 'boolean',
        'is_gmax' => 'boolean',
        'abilities' => 'array', // Automatically decode JSON when retrieving
    ];
}
