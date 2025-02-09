<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FetchPokemonTypes extends Command
{
    protected $signature = 'fetch:types';
    protected $description = 'Fetch Pokémon types from the PokéAPI and save them to the database';

    public function handle()
    {
        $this->info("Fetching Pokémon types from the PokéAPI...");

        $response = Http::get("https://pokeapi.co/api/v2/type");

        if (!$response->ok()) {
            $this->error("Failed to fetch types from PokéAPI");
            return;
        }

        $types = $response->json()['results'];

        foreach ($types as $type) {
            $typeName = $type['name'];
            $typeDetails = Http::get($type['url'])->json();

            $damageRelations = $typeDetails['damage_relations'];

            // Extract damage relationships
            $noEffectTo = $this->extractTypeNames($damageRelations['no_damage_to']);
            $notVeryEffectiveTo = $this->extractTypeNames($damageRelations['half_damage_to']);
            $superEffectiveTo = $this->extractTypeNames($damageRelations['double_damage_to']);
            $immuneTo = $this->extractTypeNames($damageRelations['no_damage_from']);
            $resistsTo = $this->extractTypeNames($damageRelations['half_damage_from']);
            $weakTo = $this->extractTypeNames($damageRelations['double_damage_from']);

            // Determine category (Physical or Special, applicable in Gen 1-3)
            $category = $this->getTypeCategory($typeName);

            // Insert or update the type in the database
            DB::table('types')->updateOrInsert(
                ['type' => $typeName],
                [
                    'no_effect_to' => json_encode($noEffectTo),
                    'not_very_effective_to' => json_encode($notVeryEffectiveTo),
                    'super_effective_to' => json_encode($superEffectiveTo),
                    'immune_to' => json_encode($immuneTo),
                    'resists_to' => json_encode($resistsTo),
                    'weak_to' => json_encode($weakTo),
                    'category' => $category
                ]
            );

            $this->info("Stored type: {$typeName}");
        }

        $this->info("Completed fetching and storing Pokémon types!");
    }

    private function extractTypeNames($typeArray)
    {
        return array_map(fn($type) => $type['name'], $typeArray);
    }

    private function getTypeCategory($typeName)
    {
        $physicalTypes = ['normal', 'fighting', 'flying', 'poison', 'ground', 'rock', 'bug', 'ghost', 'steel'];
        $specialTypes = ['fire', 'water', 'electric', 'grass', 'ice', 'psychic', 'dragon', 'dark'];

        if (in_array($typeName, $physicalTypes)) {
            return 'physical';
        } elseif (in_array($typeName, $specialTypes)) {
            return 'special';
        }

        return null; // Default for newer generations
    }
}
