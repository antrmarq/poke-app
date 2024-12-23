<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PokemonController extends Controller
{
    public function getPokemon($query)
    {
        if (is_numeric($query) && $query > 0 && $query < 1025) {
            $pokemon = DB::table('pokemon')->select(['id', 'name', 'type1', 'type2', 'sprite_url', 'abilities'])->where('id', $query)->first();
        } 
        else if ($query == 'random') {
            $pokemon = DB::table('pokemon')->inRandomOrder()->first();
        } 
        else {
            $pokemon = DB::table('pokemon')->where('name', $query)->first();
        }

        if ($pokemon) {
            // Capitalize the name and format types
            $pokemon->name = ucwords(str_replace('-', ' ', $pokemon->name));
            $pokemon->type1 = ucwords($pokemon->type1);
            $pokemon->type2 = ucwords($pokemon->type2);
            return response()->json($pokemon);
        } else {
            // Fuzzy matching with Levenshtein Distance
            $allPokemon = DB::table('pokemon')->get(['name']);
            $bestMatch = null;
            $shortestDistance = PHP_INT_MAX;

            foreach ($allPokemon as $p) {
                $distance = levenshtein(strtolower($query), strtolower($p->name));
                if ($distance < $shortestDistance) {
                    $shortestDistance = $distance;
                    $bestMatch = $p;
                }
            }

            if ($bestMatch && $shortestDistance <= 3) { // Threshold for similarity
                $bestMatch->name = ucwords(str_replace('-', ' ', $bestMatch->name));
                return response()->json([
                    'error' => 'Pokemon not found, but did you mean this?',
                    'suggestion' => $bestMatch
                ], 404);
            }

            return response()->json(['error' => 'Pokemon not found'], 404);
        }
    }


}

