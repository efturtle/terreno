<?php

namespace Tests\Feature;

use App\Models\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertySearchTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that bedroom search returns minimum bedrooms (1+ behavior).
     */
    public function test_bedroom_search_returns_minimum_bedrooms(): void
    {
        // Create properties with different bedroom counts
        Property::factory()->create(['bedrooms' => 1, 'city' => 'Test City']);
        Property::factory()->create(['bedrooms' => 2, 'city' => 'Test City']);
        Property::factory()->create(['bedrooms' => 3, 'city' => 'Test City']);
        Property::factory()->create(['bedrooms' => 4, 'city' => 'Test City']);
        Property::factory()->create(['bedrooms' => 5, 'city' => 'Test City']);

        // Search for 3+ bedrooms
        $response = $this->getJson('/api/properties?bedrooms=3');

        $response->assertStatus(200);
        $data = $response->json('data');
        
        // Should return properties with 3, 4, and 5 bedrooms (3 properties)
        $this->assertCount(3, $data);
        
        // Verify all returned properties have 3 or more bedrooms
        foreach ($data as $property) {
            $this->assertGreaterThanOrEqual(3, $property['property_details']['bedrooms']);
        }
    }

    /**
     * Test that bathroom search returns minimum bathrooms (1+ behavior).
     */
    public function test_bathroom_search_returns_minimum_bathrooms(): void
    {
        // Create properties with different bathroom counts
        Property::factory()->create(['bathrooms' => 1, 'city' => 'Test City']);
        Property::factory()->create(['bathrooms' => 2, 'city' => 'Test City']);
        Property::factory()->create(['bathrooms' => 3, 'city' => 'Test City']);
        Property::factory()->create(['bathrooms' => 4, 'city' => 'Test City']);

        // Search for 2+ bathrooms
        $response = $this->getJson('/api/properties?bathrooms=2');

        $response->assertStatus(200);
        $data = $response->json('data');
        
        // Should return properties with 2, 3, and 4 bathrooms (3 properties)
        $this->assertCount(3, $data);
        
        // Verify all returned properties have 2 or more bathrooms
        foreach ($data as $property) {
            $this->assertGreaterThanOrEqual(2, $property['property_details']['bathrooms']);
        }
    }

    /**
     * Test combined bedroom and bathroom search.
     */
    public function test_combined_bedroom_bathroom_search(): void
    {
        // Create properties with various combinations
        Property::factory()->create(['bedrooms' => 2, 'bathrooms' => 1]); // Should NOT match
        Property::factory()->create(['bedrooms' => 3, 'bathrooms' => 2]); // Should match
        Property::factory()->create(['bedrooms' => 4, 'bathrooms' => 3]); // Should match
        Property::factory()->create(['bedrooms' => 2, 'bathrooms' => 3]); // Should NOT match (bedrooms too low)
        Property::factory()->create(['bedrooms' => 5, 'bathrooms' => 1]); // Should NOT match (bathrooms too low)

        // Search for 3+ bedrooms AND 2+ bathrooms
        $response = $this->getJson('/api/properties?bedrooms=3&bathrooms=2');

        $response->assertStatus(200);
        $data = $response->json('data');
        
        // Should return 2 properties that match both criteria
        $this->assertCount(2, $data);
        
        // Verify all returned properties meet both criteria
        foreach ($data as $property) {
            $this->assertGreaterThanOrEqual(3, $property['property_details']['bedrooms']);
            $this->assertGreaterThanOrEqual(2, $property['property_details']['bathrooms']);
        }
    }

    /**
     * Test edge case: search for 1 bedroom should return all properties.
     */
    public function test_one_bedroom_search_returns_all_properties(): void
    {
        // Create properties with 1, 2, 3 bedrooms
        Property::factory()->create(['bedrooms' => 1]);
        Property::factory()->create(['bedrooms' => 2]);
        Property::factory()->create(['bedrooms' => 3]);

        // Search for 1+ bedrooms (should return all)
        $response = $this->getJson('/api/properties?bedrooms=1');

        $response->assertStatus(200);
        $data = $response->json('data');
        
        // Should return all 3 properties
        $this->assertCount(3, $data);
    }

    /**
     * Test edge case: search for high bedroom count returns only matching properties.
     */
    public function test_high_bedroom_search_returns_limited_results(): void
    {
        // Create properties with 1, 2, 3 bedrooms
        Property::factory()->create(['bedrooms' => 1]);
        Property::factory()->create(['bedrooms' => 2]);
        Property::factory()->create(['bedrooms' => 3]);

        // Search for 5+ bedrooms (should return none)
        $response = $this->getJson('/api/properties?bedrooms=5');

        $response->assertStatus(200);
        $data = $response->json('data');
        
        // Should return no properties
        $this->assertCount(0, $data);
    }
}