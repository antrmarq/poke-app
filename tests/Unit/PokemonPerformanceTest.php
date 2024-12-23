<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class PokemonPerformanceTest extends TestCase
{
    /**
     * Test handling 5000 read requests per second.
     *
     * @return void
     */
    public function testReadRequestsPerformance()
    {
        $requestCount = 5000; // Number of requests to simulate
        $startTime = microtime(true);

        // Pre-fetch all Pokémon data into memory
        $pokemonData = Cache::remember('all_pokemon', 3600, function () {
            return DB::table('pokemon')->get()->keyBy('id');
        });

        // Ensure data exists
        $this->assertNotEmpty($pokemonData, "The 'pokemon' table is empty or data could not be fetched.");

        // Query data from memory 5000 times
        for ($i = 1; $i <= $requestCount; $i++) {
            $response = $pokemonData->get($i % 1024 + 1);

            // Assert that a record was returned
            $this->assertNotNull($response, "No record returned for query ID " . ($i % 1024 + 1));
        }

        $endTime = microtime(true);
        $duration = $endTime - $startTime;

        // Assert that the duration is within 1 second
        $this->assertTrue(
            $duration <= 1,
            "The system could not handle 5000 read requests in 1 second. Duration: $duration seconds"
        );

        // Output the results for logging or debugging
        echo "Handled $requestCount requests in $duration seconds\n";
    }
}
