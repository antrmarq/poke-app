<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Loop through all generations and add the `evolution_line` column
        for ($gen = 1; $gen <= 9; $gen++) {
            $tableName = "gen_{$gen}_pokemon";

            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->json('evolution_line')->nullable()->after('hatch_counter');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Loop through all generations and remove the `evolution_line` column
        for ($gen = 1; $gen <= 9; $gen++) {
            $tableName = "gen_{$gen}_pokemon";

            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('evolution_line');
                });
            }
        }
    }
};
