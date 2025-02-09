<?php
// php artisan fetch:pokemon 1
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pokemon;
use App\Models\Game;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FetchPokemonData extends Command
{
    protected $signature = 'fetch:pokemon {count=151}';
    protected $description = 'Fetch Pokémon data from the PokéAPI and save it to the database';

    public function handle()
    {
        $count = (int) $this->argument('count');
        $this->info("Fetching data for the first {$count} Pokémon...");

        for ($id = 1; $id <= $count; $id++) {
            $response = Http::get("https://pokeapi.co/api/v2/pokemon/{$id}");
            $speciesResponse = Http::get("https://pokeapi.co/api/v2/pokemon-species/{$id}");

            if ($response->ok() && $speciesResponse->ok()) {
                $data = $response->json();
                $speciesData = $speciesResponse->json();

                // Extract gender ratio
                $maleRatio = ($speciesData['gender_rate'] !== -1) ? (8 - $speciesData['gender_rate']) * 12.5 : null;
                $femaleRatio = ($speciesData['gender_rate'] !== -1) ? $speciesData['gender_rate'] * 12.5 : null;
                $isGenderless = $speciesData['gender_rate'] === -1;
                DB::transaction(function () use ($data, $speciesData, $maleRatio, $femaleRatio, $isGenderless) {
                    // Get the Pokémon's generation debut
                    $startingGeneration = $this->getGenerationNumber($speciesData['generation']['name']);
                
                    // Save the Pokémon into the main table
                    $pokemon = Pokemon::updateOrCreate(
                        ['id' => $data['id']],
                        [
                            'name' => $data['name'],
                            'genus' => $this->getGenus($speciesData),
                            'generation_debut' => $startingGeneration,
                            'height' => $data['height'],
                            'weight' => $data['weight'],
                            'is_baby' => $speciesData['is_baby'],
                            'is_legendary' => $speciesData['is_legendary'],
                            'is_mythical' => $speciesData['is_mythical'],
                            'is_fossil' => false, // Placeholder
                            'is_ultra_beast' => false, // Placeholder
                            'is_mega' => false, // Placeholder
                            'is_primal' => false, // Placeholder
                            'is_gmax' => false, // Placeholder
                            'color' => $speciesData['color']['name'] ?? null,
                            'shape' => $speciesData['shape']['name'] ?? null,
                            'habitat' => $speciesData['habitat']['name'] ?? null,
                            'male_gender_ratio' => $maleRatio,
                            'female_gender_ratio' => $femaleRatio,
                            'is_genderless' => $isGenderless,
                        ]
                    );
                
                    // Insert into all generations from their debut up to Gen 9
                    for ($gen = $startingGeneration; $gen <= 9; $gen++) {
                        $generationTable = "gen_{$gen}_pokemon";
                
                        // Ensure the table exists
                        if (Schema::hasTable($generationTable)) {
                            DB::table($generationTable)->updateOrInsert(
                                ['pokemon_id' => $pokemon->id],
                                [
                                    'type1' => $data['types'][0]['type']['name'] ?? null,
                                    'type2' => $data['types'][1]['type']['name'] ?? null,
                                    'hp' => $data['stats'][0]['base_stat'],
                                    'attack' => $data['stats'][1]['base_stat'],
                                    'defense' => $data['stats'][2]['base_stat'],
                                    'special_attack' => $data['stats'][3]['base_stat'],
                                    'special_defense' => $data['stats'][4]['base_stat'],
                                    'speed' => $data['stats'][5]['base_stat'],
                                    'base_experience' => $data['base_experience'],
                                    'capture_rate' => $speciesData['capture_rate'],
                                    'base_happiness' => $speciesData['base_happiness'],
                                    'growth_rate' => $this->getGrowthRate($speciesData),
                                    'is_genderless' => $isGenderless,
                                    'male_gender_ratio' => $maleRatio,
                                    'female_gender_ratio' => $femaleRatio,
                                    'has_regional' => false, // Placeholder
                                    'hatch_counter' => $speciesData['hatch_counter'] * 255,
                                    'abilities' => json_encode($this->extractAbilities($data)), // JSON encode abilities
                                    'sprite_url' => $data['sprites']['front_default'] ?? null,
                                ]
                            );
                        } else {
                            Log::error("Table {$generationTable} does not exist. Skipping...");
                        }
                    }
                });                
                $this->info("Fetched: {$data['name']} (ID: {$data['id']})");
            } else {
                $this->error("Failed to fetch data for Pokémon ID: {$id}");
            }
        }

        $this->info("Completed fetching Pokémon data!");
    }

    private function getGenus($speciesData)
    {
        foreach ($speciesData['genera'] as $genus) {
            if ($genus['language']['name'] === 'en') {
                return $genus['genus'];
            }
        }
        return null;
    }

    private function getGrowthRate($speciesData)
    {
        return str_replace('https://pokeapi.co/api/v2/growth-rate/', '', rtrim($speciesData['growth_rate']['url'], '/'));
    }

    private function extractAbilities($data)
    {
        return array_map(function ($ability) {
            return [
                'name' => $ability['ability']['name'],
                'is_hidden' => $ability['is_hidden']
            ];
        }, $data['abilities']);
    }
    private function getGenerationNumber($generationName)
    {
        $generationMap = [
            'generation-i' => 1,
            'generation-ii' => 2,
            'generation-iii' => 3,
            'generation-iv' => 4,
            'generation-v' => 5,
            'generation-vi' => 6,
            'generation-vii' => 7,
            'generation-viii' => 8,
            'generation-ix' => 9,
        ];

        return $generationMap[$generationName] ?? null; // Return null if not found
    }

}
