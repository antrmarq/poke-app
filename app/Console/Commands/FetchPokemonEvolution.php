<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FetchPokemonEvolution extends Command
{
    protected $signature = 'fetch:evolution {count=151}';
    protected $description = 'Fetch Pokémon evolution lines and update the database';

    public function handle()
    {
        $count = (int) $this->argument('count');
        $this->info("Fetching evolution data for the first {$count} Pokémon...");

        for ($id = 1; $id <= $count; $id++) {
            $speciesResponse = Http::get("https://pokeapi.co/api/v2/pokemon-species/{$id}");

            if ($speciesResponse->ok()) {
                $speciesData = $speciesResponse->json();
                $evolutionChainUrl = $speciesData['evolution_chain']['url'];

                $evolutionChain = $this->fetchEvolutionChain($evolutionChainUrl);
                $generation = $this->getGenerationNumber($speciesData['generation']['name']);

                DB::transaction(function () use ($id, $evolutionChain, $generation) {
                    for ($gen = $generation; $gen <= 9; $gen++) {
                        $tableName = "gen_{$gen}_pokemon";

                        if (Schema::hasTable($tableName)) {
                            DB::table($tableName)
                                ->where('pokemon_id', $id)
                                ->update(['evolution_line' => json_encode($evolutionChain)]);
                        }
                    }
                });

                $this->info("Updated evolution line for Pokémon ID: {$id}");
            } else {
                $this->error("Failed to fetch species data for Pokémon ID: {$id}");
            }
        }

        $this->info("Completed fetching evolution data!");
    }

    private function fetchEvolutionChain($url)
    {
        $response = Http::get($url);

        if (!$response->ok()) {
            return null;
        }

        $data = $response->json();
        $chain = [];
        $current = $data['chain'];

        while ($current) {
            $pokemonName = $current['species']['name'];

            // Fetch Pokémon ID from the database
            $pokemonId = DB::table('pokemon')->where('name', $pokemonName)->value('id');

            if ($pokemonId) {
                $chain[] = $pokemonId;
            }

            $current = $current['evolves_to'][0] ?? null;
        }

        return $chain;
    }

    private function getGenerationNumber($generation)
    {
        $romanToNumber = [
            'generation-i' => 1,
            'generation-ii' => 2,
            'generation-iii' => 3,
            'generation-iv' => 4,
            'generation-v' => 5,
            'generation-vi' => 6,
            'generation-vii' => 7,
            'generation-viii' => 8,
            'generation-ix' => 9
        ];

        return $romanToNumber[$generation] ?? null;
    }
}
