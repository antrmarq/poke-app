<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pokemon extends Model
{
    use HasFactory;

    // Attributes allowed for mass assignment
    protected $fillable = [
        'id',          // Pokémon ID
        'name',        // Pokémon name
        'type1',       // Primary type
        'type2',       // Secondary type
        'abilities',   // JSON-encoded abilities
        'sprite_url',  // URL for the Pokémon sprite
    ];
}

