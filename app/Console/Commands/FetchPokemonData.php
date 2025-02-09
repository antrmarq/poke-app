<?php
// php artisan fetch:pokemon 1025
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pokemon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchPokemonData extends Command
{
    protected $signature = 'fetch:pokemon {count=10}';
    protected $description = 'Fetch Pokémon data from the PokéAPI and save to the database';

    public function handle()
    {
        $count = $this->argument('count');
        $this->info("Fetching data for the first {$count} Pokémon...");

        for ($id = 1; $id <= $count; $id++) {
            $pokemonResponse = Http::get("https://pokeapi.co/api/v2/pokemon/{$id}");
            $speciesResponse = Http::get("https://pokeapi.co/api/v2/pokemon-species/{$id}");

            if ($pokemonResponse->ok() && $speciesResponse->ok()) {
                $pokemonData = $pokemonResponse->json();
                $speciesData = $speciesResponse->json();

                // Extract abilities as JSON
                $abilities = json_encode(array_map(function ($ability) {
                    return [
                        'name' => $ability['ability']['name'],
                        'is_hidden' => $ability['is_hidden']
                    ];
                }, $pokemonData['abilities']));

                // Determine gender ratios
                $genderRatioMale = 100 - ($speciesData['gender_rate'] * 12.5);
                $genderRatioFemale = $speciesData['gender_rate'] * 12.5;

                // Determine if Pokémon is genderless
                $isGenderless = $speciesData['gender_rate'] === -1;

                // Determine if the Pokémon has specific features
                $isBaby = $speciesData['is_baby'];
                $isLegendary = $speciesData['is_legendary'];
                $isMythical = $speciesData['is_mythical'];

                // Save or update the Pokémon in the database
                Pokemon::updateOrCreate(
                    ['id' => $pokemonData['id']],
                    [
                        'name' => $pokemonData['name'],
                        'type1' => $pokemonData['types'][0]['type']['name'] ?? null,
                        'type2' => $pokemonData['types'][1]['type']['name'] ?? null,
                        'type_gen_6_a' => $pokemonData['types'][0]['type']['name'] ?? null, // Same as type1 for now
                        'type_gen_6_b' => $pokemonData['types'][1]['type']['name'] ?? null, // Same as type2 for now
                        'genus' => $this->getGenus($speciesData),
                        'generation_debut' => $this->getGeneration($speciesData),
                        'height' => $pokemonData['height'],
                        'weight' => $pokemonData['weight'],
                        'is_genderless' => $isGenderless,
                        'male_gender_ratio' => $isGenderless ? null : $genderRatioMale,
                        'female_gender_ration' => $isGenderless ? null : $genderRatioFemale,
                        'is_baby' => $isBaby,
                        'is_legendary' => $isLegendary,
                        'is_mythical' => $isMythical,
                        'is_fossil' => false, // Placeholder (no API support currently)
                        'is_ultra_beast' => false, // Placeholder (no API support currently)
                        'is_mega' => false, // Placeholder (needs additional data)
                        'is_primal' => false, // Placeholder (needs additional data)
                        'is_gmax' => false, // Placeholder (needs additional data)
                        'has_regional' => false, // Placeholder (needs additional data)
                        'hatch_counter' => $speciesData['hatch_counter'] * 255,
                        'color' => $speciesData['color']['name'] ?? null,
                        'shape' => $speciesData['shape']['name'] ?? null,
                        'habitat' => $speciesData['habitat']['name'] ?? null,
                        'abilities' => $abilities,
                        'sprite_url' => $pokemonData['sprites']['front_default'] ?? null,
                    ]
                );

                $this->info("Fetched and saved: {$pokemonData['name']} (ID: {$pokemonData['id']})");
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

    private function getGeneration($speciesData)
    {
        $generationUrlParts = explode('/', $speciesData['generation']['url']);
        return intval(end($generationUrlParts));
    }
}
