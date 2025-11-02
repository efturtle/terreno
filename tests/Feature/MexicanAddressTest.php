<?php

namespace Tests\Feature;

use App\Models\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MexicanAddressTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that Mexican addresses are generated correctly.
     */
    public function test_generates_realistic_mexican_addresses(): void
    {
        $properties = Property::factory(10)->create();

        foreach ($properties as $property) {
            // Check that zip codes follow Mexican format
            $this->assertMatchesRegularExpression('/^MX-\d{5}$/', $property->zip_code);
            
            // Check that state is a valid Mexican state
            $mexicanStates = [
                'Aguascalientes', 'Baja California', 'Baja California Sur', 'Campeche',
                'Chiapas', 'Chihuahua', 'Ciudad de México', 'Coahuila', 'Colima',
                'Durango', 'Estado de México', 'Guanajuato', 'Guerrero', 'Hidalgo',
                'Jalisco', 'Michoacán', 'Morelos', 'Nayarit', 'Nuevo León', 'Oaxaca',
                'Puebla', 'Querétaro', 'Quintana Roo', 'San Luis Potosí', 'Sinaloa',
                'Sonora', 'Tabasco', 'Tamaulipas', 'Tlaxcala', 'Veracruz', 'Yucatán', 'Zacatecas'
            ];
            $this->assertContains($property->state, $mexicanStates);
            
            // Check that property types are in Spanish
            $this->assertContains($property->property_type, ['casa', 'condominio', 'departamento', 'townhouse', 'duplex']);
            
            // Check that status is in Spanish
            $this->assertContains($property->status, ['disponible', 'pendiente', 'vendida', 'rentada']);
            
            // Check that addresses follow Mexican format
            $this->assertMatchesRegularExpression('/^(Calle|Avenida|Boulevard|Privada|Cerrada|Callejón)/', $property->address);
        }
    }

    /**
     * Test that zip codes match their states correctly.
     */
    public function test_zip_codes_match_states(): void
    {
        $property = Property::factory()->inState('Jalisco')->create();
        
        // Jalisco zip codes should start with 44-49
        $zipNumber = str_replace('MX-', '', $property->zip_code);
        $firstTwo = substr($zipNumber, 0, 2);
        $this->assertTrue(
            in_array($firstTwo, ['44', '45', '46', '47', '48', '49']),
            "Jalisco zip code should start with 44-49, got: {$firstTwo}"
        );
    }

    /**
     * Test that cities match their states.
     */
    public function test_cities_match_states(): void
    {
        $property = Property::factory()->inState('Nuevo León')->create();
        
        $expectedCities = ['Monterrey', 'Guadalupe', 'San Nicolás de los Garza', 'Apodaca', 'Santa Catarina'];
        $this->assertContains($property->city, $expectedCities);
    }

    /**
     * Test that coordinates are within Mexico.
     */
    public function test_coordinates_are_within_mexico(): void
    {
        $property = Property::factory()->create();
        
        // Mexico latitude range: 14.5° to 32.7° N
        $this->assertGreaterThanOrEqual(14.5, $property->latitude);
        $this->assertLessThanOrEqual(32.7, $property->latitude);
        
        // Mexico longitude range: -118.4° to -86.7° W
        $this->assertGreaterThanOrEqual(-118.4, $property->longitude);
        $this->assertLessThanOrEqual(-86.7, $property->longitude);
    }
}