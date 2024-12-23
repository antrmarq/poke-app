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
            $response = Http::get("https://pokeapi.co/api/v2/pokemon/{$id}");

            if ($response->ok()) {
                $data = $response->json();
                Log::info($data);
                // Save or update the Pokémon in the database
                Pokemon::updateOrCreate(
                    ['id' => $data['id']], // Match by Pokémon ID
                    [
                        'name' => $data['name'],
                        'type1' => $data['types'][0]['type']['name'] ?? null,
                        'type2' => $data['types'][1]['type']['name'] ?? null,
                        'abilities' => json_encode(array_column($data['abilities'], 'ability', 'name')),
                        'sprite_url' => $data['sprites']['front_default'] ?? null,
                    ]
                );

                $this->info("Fetched: {$data['name']} (ID: {$data['id']})");
            } else {
                $this->error("Failed to fetch data for Pokémon ID: {$id}");
            }
        }

        $this->info("Completed fetching Pokémon data!");
    }
}
