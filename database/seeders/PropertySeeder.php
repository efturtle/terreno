<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PropertySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create regular properties
        \App\Models\Property::factory(1000)->create();

        // Create some luxury properties
        \App\Models\Property::factory(500)->luxury()->create();

        // Create some properties for sale
        \App\Models\Property::factory(1110)->forSale()->create();

        // Create some properties for rent
        \App\Models\Property::factory(800)->forRent()->create();

        // Create some sold properties
        \App\Models\Property::factory(300)->sold()->create();
    }
}
