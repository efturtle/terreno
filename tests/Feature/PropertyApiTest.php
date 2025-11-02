<?php

namespace Tests\Feature;

use App\Models\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PropertyApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_can_list_properties(): void
    {
        Property::factory(5)->create();

        $response = $this->getJson('/api/properties');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'address',
                        'property_details' => [
                            'square_feet',
                            'bedrooms',
                            'bathrooms',
                            'floors',
                        ],
                        'financial' => [
                            'price',
                            'price_per_sqft',
                        ],
                        'status',
                    ],
                ],
                'meta',
            ]);
    }

    public function test_can_create_property(): void
    {
        $propertyData = [
            'title' => 'Beautiful Family Home',
            'description' => 'A lovely 3-bedroom house in a quiet neighborhood',
            'address' => '123 Main St',
            'city' => 'Springfield',
            'state' => 'IL',
            'zip_code' => '62701',
            'square_feet' => 1500,
            'bedrooms' => 3,
            'bathrooms' => 2,
            'floors' => 2,
            'price' => 250000,
            'property_type' => 'casa',
            'status' => 'disponible',
            'year_built' => 2000,
            'has_basement' => true,
            'features' => ['hardwood_floors', 'granite_countertops'],
        ];

        $response = $this->postJson('/api/properties', $propertyData);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'title' => 'Beautiful Family Home',
                'square_feet' => 1500,
                'bedrooms' => 3,
            ]);

        $this->assertDatabaseHas('properties', [
            'title' => 'Beautiful Family Home',
            'square_feet' => 1500,
        ]);
    }

    public function test_can_show_property(): void
    {
        $property = Property::factory()->create([
            'title' => 'Test Property',
            'square_feet' => 2000,
        ]);

        $response = $this->getJson("/api/properties/{$property->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $property->id,
                'title' => 'Test Property',
            ]);
    }

    public function test_can_update_property(): void
    {
        $property = Property::factory()->create(['title' => 'Original Title']);

        $updateData = [
            'title' => 'Updated Title',
            'bedrooms' => 4,
        ];

        $response = $this->putJson("/api/properties/{$property->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'title' => 'Updated Title',
                'bedrooms' => 4,
            ]);

        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'title' => 'Updated Title',
            'bedrooms' => 4,
        ]);
    }

    public function test_can_delete_property(): void
    {
        $property = Property::factory()->create();

        $response = $this->deleteJson("/api/properties/{$property->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('properties', ['id' => $property->id]);
    }

    public function test_can_filter_properties_by_city(): void
    {
        Property::factory()->create(['city' => 'Chicago']);
        Property::factory()->create(['city' => 'New York']);
        Property::factory()->create(['city' => 'Chicago']);

        $response = $this->getJson('/api/properties?city=Chicago');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(2, $data);
    }

    public function test_can_filter_properties_by_price_range(): void
    {
        Property::factory()->create(['price' => 100000]);
        Property::factory()->create(['price' => 250000]);
        Property::factory()->create(['price' => 500000]);

        $response = $this->getJson('/api/properties?min_price=200000&max_price=300000');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals(250000, $data[0]['financial']['price']);
    }

    public function test_can_search_properties(): void
    {
        Property::factory()->create(['title' => 'Beautiful Ocean View']);
        Property::factory()->create(['title' => 'Mountain Cabin']);
        Property::factory()->create(['description' => 'Located near the beautiful lake']);

        $response = $this->getJson('/api/properties/search?q=beautiful');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(2, $data);
    }

    public function test_can_get_property_stats(): void
    {
        Property::factory(5)->create(['status' => 'available']);
        Property::factory(3)->create(['status' => 'sold']);
        Property::factory(2)->create(['status' => 'rented']);

        $response = $this->getJson('/api/properties/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'total_properties',
                    'available_properties',
                    'sold_properties',
                    'rented_properties',
                    'average_price',
                    'property_types',
                ],
            ]);

        $data = $response->json('data');
        $this->assertEquals(10, $data['total_properties']);
        $this->assertEquals(5, $data['available_properties']);
        $this->assertEquals(3, $data['sold_properties']);
    }

    public function test_validates_property_creation(): void
    {
        $invalidData = [
            'square_feet' => -100, // Invalid: negative
            'bedrooms' => -1, // Invalid: negative
            'property_type' => 'invalid_type', // Invalid: not in enum
            'latitude' => 100, // Invalid: out of range
        ];

        $response = $this->postJson('/api/properties', $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'square_feet',
                'bedrooms',
                'property_type',
                'latitude',
            ]);
    }
}
