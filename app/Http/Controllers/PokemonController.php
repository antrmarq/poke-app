<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PokemonController extends Controller
{
    public function getPokemon($query)
    {
        // Determine if the input is a number (ID) or a name
        if (is_numeric($query) && $query > 0) {
            $pokemon = DB::table('pokemon')->where('id', $query)->first();
        } 
        else if ($query === 'random') {
            $pokemon = DB::table('pokemon')->inRandomOrder()->first();
        } 
        else {
            $pokemon = DB::table('pokemon')->where('name', $query)->first();
        }

        // If the Pokémon exists, fetch additional data
        if ($pokemon) {
            $generationTable = 'gen_' . $pokemon->generation_debut . '_pokemon';

            // Fetch generation-specific data
            $genData = DB::table($generationTable)->where('pokemon_id', $pokemon->id)->first();

            // Fetch evolution line
            $evolutionLine = DB::table($generationTable)
                ->where('evolution_line', $genData->evolution_line ?? $pokemon->id)
                ->pluck('pokemon_id');

            // Fetch types
            $typeData = DB::table('types')
                ->whereIn('type', [$genData->type1 ?? null, $genData->type2 ?? null])
                ->get();

            // Fetch abilities
            $abilities = json_decode($genData->abilities ?? '[]', true);

            // Extract base stats
            $stats = [
                'hp' => $genData->hp ?? null,
                'attack' => $genData->attack ?? null,
                'defense' => $genData->defense ?? null,
                'special_attack' => $genData->special_attack ?? null,
                'special_defense' => $genData->special_defense ?? null,
                'speed' => $genData->speed ?? null,
            ];

            return response()->json([
                'id' => $pokemon->id,
                'name' => ucwords(str_replace('-', ' ', $pokemon->name)),
                'genus' => $pokemon->genus,
                'generation' => $pokemon->generation_debut,
                'height' => $pokemon->height,
                'weight' => $pokemon->weight,
                'types' => $typeData,
                'abilities' => $abilities,
                'evolution_line' => $evolutionLine,
                'sprite_url' => $genData->sprite_url ?? null,
                'stats' => $stats // Include stats in response
            ]);
        } 

        // If not found, attempt fuzzy matching
        return $this->handleFuzzyMatch($query);
    }


    /**
     * Handles fuzzy matching for misspelled Pokémon names.
     */
    private function handleFuzzyMatch($query)
    {
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

        if ($bestMatch && $shortestDistance <= 3) { // Allow small typos
            return response()->json([
                'error' => 'Pokemon not found, but did you mean this?',
                'suggestion' => ucwords(str_replace('-', ' ', $bestMatch->name))
            ], 404);
        }

        return response()->json(['error' => 'Pokemon not found'], 404);
    }

    /**
     * Computes weaknesses, resistances, and immunities based on Pokémon types.
     */
    private function calculateTypeEffectiveness($types)
    {
        // Type chart for effectiveness calculations
        $TYPE_CHART = [
            "normal" => ["weak" => ["fighting"], "resist" => [], "immune" => ["ghost"]],
            "fire" => ["weak" => ["water", "rock", "ground"], "resist" => ["fire", "grass", "bug", "steel", "fairy", "ice"], "immune" => []],
            "water" => ["weak" => ["electric", "grass"], "resist" => ["fire", "water", "ice", "steel"], "immune" => []],
            "electric" => ["weak" => ["ground"], "resist" => ["electric", "flying", "steel"], "immune" => []],
            "grass" => ["weak" => ["fire", "ice", "poison", "flying", "bug"], "resist" => ["water", "grass", "electric", "ground"], "immune" => []],
            "ice" => ["weak" => ["fire", "fighting", "rock", "steel"], "resist" => ["ice"], "immune" => []],
            "fighting" => ["weak" => ["flying", "psychic", "fairy"], "resist" => ["rock", "bug", "dark"], "immune" => []],
            "poison" => ["weak" => ["ground", "psychic"], "resist" => ["fighting", "poison", "bug", "grass", "fairy"], "immune" => []],
            "ground" => ["weak" => ["water", "grass", "ice"], "resist" => ["poison", "rock"], "immune" => ["electric"]],
            "flying" => ["weak" => ["electric", "ice", "rock"], "resist" => ["fighting", "bug", "grass"], "immune" => ["ground"]],
            "psychic" => ["weak" => ["bug", "ghost", "dark"], "resist" => ["fighting", "psychic"], "immune" => []],
            "bug" => ["weak" => ["fire", "flying", "rock"], "resist" => ["fighting", "grass", "ground"], "immune" => []],
            "rock" => ["weak" => ["water", "grass", "fighting", "ground", "steel"], "resist" => ["normal", "fire", "poison", "flying"], "immune" => []],
            "ghost" => ["weak" => ["ghost", "dark"], "resist" => ["poison", "bug"], "immune" => ["normal", "fighting"]],
            "dragon" => ["weak" => ["ice", "dragon", "fairy"], "resist" => ["fire", "water", "electric", "grass"], "immune" => []],
            "dark" => ["weak" => ["fighting", "bug", "fairy"], "resist" => ["ghost", "dark"], "immune" => ["psychic"]],
            "steel" => ["weak" => ["fire", "fighting", "ground"], "resist" => ["normal", "grass", "ice", "flying", "psychic", "bug", "rock", "dragon", "steel", "fairy"], "immune" => ["poison"]],
            "fairy" => ["weak" => ["poison", "steel"], "resist" => ["fighting", "bug", "dark"], "immune" => ["dragon"]]
        ];

        $effectiveness = [
            "doubleWeaknesses" => [],
            "weaknesses" => [],
            "resistances" => [],
            "doubleResistances" => [],
            "immunities" => []
        ];

        foreach ($types as $type) {
            if (!isset($TYPE_CHART[$type])) continue;

            foreach ($TYPE_CHART[$type]["weak"] as $weak) {
                if (in_array($weak, $effectiveness["resistances"])) {
                    $effectiveness["resistances"] = array_diff($effectiveness["resistances"], [$weak]);
                } elseif (in_array($weak, $effectiveness["weaknesses"])) {
                    $effectiveness["doubleWeaknesses"][] = $weak;
                } else {
                    $effectiveness["weaknesses"][] = $weak;
                }
            }

            foreach ($TYPE_CHART[$type]["resist"] as $resist) {
                if (in_array($resist, $effectiveness["weaknesses"])) {
                    $effectiveness["weaknesses"] = array_diff($effectiveness["weaknesses"], [$resist]);
                } elseif (in_array($resist, $effectiveness["resistances"])) {
                    $effectiveness["doubleResistances"][] = $resist;
                } else {
                    $effectiveness["resistances"][] = $resist;
                }
            }

            $effectiveness["immunities"] = array_merge($effectiveness["immunities"], $TYPE_CHART[$type]["immune"]);
        }

        return $effectiveness;
    }
}
