<?php

namespace Tests\Feature;

use App\Models\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArchitectureExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Demonstrates how Form Requests validate data BEFORE reaching controller
     */
    public function test_form_request_validation_prevents_bad_data(): void
    {
        // This will be rejected by StorePropertyRequest validation
        $badData = [
            'square_feet' => -100,        // Invalid: negative
            'property_type' => 'castle',  // Invalid: not in allowed types
            'year_built' => 2030,        // Invalid: future year
        ];

        $response = $this->postJson('/api/properties', $badData);

        // Form Request stops this BEFORE hitting the controller
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['square_feet', 'property_type', 'year_built']);
        
        // No property was created because validation failed
        $this->assertDatabaseCount('properties', 0);
    }

    /**
     * Demonstrates how Resources transform raw model data into clean JSON
     */
    public function test_resource_transforms_model_to_clean_json(): void
    {
        // Create a property with specific data to test transformation
        $property = Property::factory()->create([
            'title' => 'Test Property',
            'square_feet' => 2000,
            'price' => 400000,
            'bedrooms' => 3,
            'bathrooms' => 2,
        ]);

        $response = $this->getJson("/api/properties/{$property->id}");

        // PropertyResource transforms this into organized structure
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         'id',
                         'title',
                         'property_details' => [
                             'square_feet',
                             'bedrooms',
                             'bathrooms',
                         ],
                         'financial' => [
                             'price',
                             'price_per_sqft',
                         ],
                         'address' => [
                             'street',
                             'city',
                             'full_address',
                         ],
                     ]
                 ]);

        // Verify the data is properly structured and formatted
        $data = $response->json('data');
        $this->assertEquals('Test Property', $data['title']);
        $this->assertEquals(2000, $data['property_details']['square_feet']);
        $this->assertEquals(3, $data['property_details']['bedrooms']);
        $this->assertEquals('400000.00', $data['financial']['price']);
        
        // The key point: Resource organized raw model data into logical groups
        $this->assertArrayHasKey('property_details', $data);
        $this->assertArrayHasKey('financial', $data);
        $this->assertArrayHasKey('address', $data);
        $this->assertArrayHasKey('amenities', $data);
    }

    /**
     * Demonstrates how indexes speed up filtered queries
     */
    public function test_database_indexes_enable_fast_filtering(): void
    {
        // Create properties with different characteristics
        Property::factory()->create(['city' => 'Chicago', 'bedrooms' => 3, 'status' => 'available']);
        Property::factory()->create(['city' => 'Chicago', 'bedrooms' => 2, 'status' => 'sold']);
        Property::factory()->create(['city' => 'New York', 'bedrooms' => 3, 'status' => 'available']);
        Property::factory()->create(['city' => 'Chicago', 'bedrooms' => 3, 'status' => 'available']);

        // This query uses the indexes we created:
        // index(['city', 'state']) and index(['bedrooms', 'bathrooms'])
        $response = $this->getJson('/api/properties?city=Chicago&bedrooms=3&status=available');

        $response->assertStatus(200);
        $data = $response->json('data');
        
        // Should find exactly 2 properties matching all criteria
        $this->assertCount(2, $data);
        
        // Verify all returned properties match the filters
        foreach ($data as $property) {
            $this->assertStringContainsString('Chicago', $property['address']['city']);
            $this->assertEquals(3, $property['property_details']['bedrooms']);
            $this->assertEquals('available', $property['status']);
        }
    }

    /**
     * Demonstrates the complete flow: Request → Form Request → Controller → Resource → Response
     */
    public function test_complete_api_flow(): void
    {
        $propertyData = [
            'title' => 'Modern Apartment',
            'square_feet' => 1200,
            'price' => 300000,
            'bedrooms' => 2,
            'bathrooms' => 1,
            'property_type' => 'departamento',
            'city' => 'San Francisco',
            'features' => ['hardwood_floors', 'balcony'],
        ];

        // 1. Request hits StorePropertyRequest
        // 2. StorePropertyRequest validates and calculates price_per_sqft
        // 3. Controller creates property with clean data
        // 4. PropertyResource formats the response
        $response = $this->postJson('/api/properties', $propertyData);

        $response->assertStatus(201)
                 ->assertJsonFragment([
                     'title' => 'Modern Apartment',
                 ]);

        // Verify the automatic calculations happened
        $data = $response->json('data');
        $this->assertEquals('250.00', $data['financial']['price_per_sqft']); // 300000/1200
        
        // Verify Resource organized the data properly
        $this->assertArrayHasKey('property_details', $data);
        $this->assertArrayHasKey('financial', $data);
        $this->assertArrayHasKey('amenities', $data);
        $this->assertEquals(['hardwood_floors', 'balcony'], $data['amenities']['features']);
    }
}