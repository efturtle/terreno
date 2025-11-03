<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Property>
 */
class PropertyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $squareFeet = fake()->numberBetween(500, 5000);
        $price = fake()->numberBetween(100000, 2000000);

        // Generate Mexican address data
        $state = $this->getMexicanState();
        $city = $this->getMexicanCity($state);
        $zipCode = $this->getMexicanZipCode($state);

        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(3),
            'address' => $this->getMexicanStreetAddress(),
            'city' => $city,
            'state' => $state,
            'zip_code' => $zipCode,
            'latitude' => fake()->latitude(14.5, 32.7), // Mexico latitude range (Chiapas to Baja California)
            'longitude' => fake()->longitude(-118.4, -86.7), // Mexico longitude range (Baja California to Quintana Roo)
            'square_feet' => $squareFeet,
            'bedrooms' => fake()->numberBetween(1, 6),
            'bathrooms' => fake()->numberBetween(1, 4),
            'floors' => fake()->numberBetween(1, 3),
            'price' => $price,
            'price_per_sqft' => round($price / $squareFeet, 2),
            'monthly_rent' => fake()->optional(0.6)->numberBetween(1500, 100000),
            'property_taxes' => fake()->numberBetween(2000, 15000),
            'property_type' => fake()->randomElement(['casa', 'condominio', 'departamento', 'townhouse', 'duplex']),
            'status' => fake()->randomElement(['disponible', 'pendiente', 'vendida', 'rentada']),
            'year_built' => fake()->numberBetween(1950, 2024),
            'lot_size' => fake()->randomFloat(2, 0.1, 2.0),
            'garage_spaces' => fake()->numberBetween(0, 3),
            'has_basement' => fake()->boolean(30),
            'has_pool' => fake()->boolean(20),
            'has_garden' => fake()->boolean(40),
            'features' => fake()->randomElements([
                'hardwood_floors',
                'granite_countertops',
                'stainless_steel_appliances',
                'walk_in_closet',
                'fireplace',
                'balcony',
                'patio',
                'air_conditioning',
                'dishwasher',
                'laundry_in_unit',
                'pet_friendly',
                'parking',
            ], fake()->numberBetween(2, 6)),
            'metadata' => [
                'mls_number' => fake()->unique()->randomNumber(8),
                'listing_agent' => fake()->name(),
                'last_renovated' => fake()->optional(0.4)->year(),
            ],
            'user_id' => null, // Can be set manually or via state
        ];
    }

    /**
     * Indicate that the property is for sale.
     */
    public function forSale(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'available',
            'monthly_rent' => null,
        ]);
    }

    /**
     * Indicate that the property is for rent.
     */
    public function forRent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'available',
            'price' => null,
            'price_per_sqft' => null,
            'monthly_rent' => fake()->numberBetween(800, 5000),
        ]);
    }

    /**
     * Indicate that the property is sold.
     */
    public function sold(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'vendida',
        ]);
    }

    /**
     * Create a luxury property.
     */
    public function luxury(): static
    {
        return $this->state(function (array $attributes) {
            $squareFeet = fake()->numberBetween(3000, 8000);
            $price = fake()->numberBetween(800000, 5000000);

            return [
                'square_feet' => $squareFeet,
                'bedrooms' => fake()->numberBetween(4, 8),
                'bathrooms' => fake()->numberBetween(3, 6),
                'floors' => fake()->numberBetween(2, 4),
                'price' => $price,
                'price_per_sqft' => round($price / $squareFeet, 2),
                'lot_size' => fake()->randomFloat(2, 0.5, 3.0),
                'garage_spaces' => fake()->numberBetween(2, 4),
                'has_basement' => fake()->boolean(60),
                'has_pool' => fake()->boolean(70),
                'has_garden' => fake()->boolean(80),
                'features' => [
                    'hardwood_floors',
                    'granite_countertops',
                    'stainless_steel_appliances',
                    'walk_in_closet',
                    'fireplace',
                    'master_suite',
                    'gourmet_kitchen',
                    'wine_cellar',
                    'home_theater',
                    'smart_home',
                ],
            ];
        });
    }

    /**
     * Configure the model factory to use a specific state and matching city/zip.
     */
    public function inState(string $state): static
    {
        return $this->state(function (array $attributes) use ($state) {
            return [
                'state' => $state,
                'city' => $this->getMexicanCity($state),
                'zip_code' => $this->getMexicanZipCode($state),
            ];
        });
    }

    /**
     * Get a random Mexican state.
     */
    private function getMexicanState(): string
    {
        $states = [
            'Aguascalientes', 'Baja California', 'Baja California Sur', 'Campeche',
            'Chiapas', 'Chihuahua', 'Ciudad de México', 'Coahuila', 'Colima',
            'Durango', 'Estado de México', 'Guanajuato', 'Guerrero', 'Hidalgo',
            'Jalisco', 'Michoacán', 'Morelos', 'Nayarit', 'Nuevo León', 'Oaxaca',
            'Puebla', 'Querétaro', 'Quintana Roo', 'San Luis Potosí', 'Sinaloa',
            'Sonora', 'Tabasco', 'Tamaulipas', 'Tlaxcala', 'Veracruz', 'Yucatán', 'Zacatecas'
        ];

        return fake()->randomElement($states);
    }

    /**
     * Get a random Mexican city based on state.
     */
    private function getMexicanCity(string $state): string
    {
        $citiesByState = [
            'Aguascalientes' => ['Aguascalientes', 'Calvillo', 'Rincón de Romos'],
            'Baja California' => ['Tijuana', 'Mexicali', 'Ensenada', 'Tecate', 'Rosarito'],
            'Baja California Sur' => ['La Paz', 'Los Cabos', 'Loreto', 'Comondú'],
            'Campeche' => ['Campeche', 'Ciudad del Carmen', 'Champotón'],
            'Chiapas' => ['Tuxtla Gutiérrez', 'San Cristóbal de las Casas', 'Tapachula', 'Comitán'],
            'Chihuahua' => ['Chihuahua', 'Ciudad Juárez', 'Delicias', 'Cuauhtémoc'],
            'Ciudad de México' => ['Álvaro Obregón', 'Azcapotzalco', 'Benito Juárez', 'Coyoacán', 'Cuauhtémoc', 'Miguel Hidalgo', 'Tlalpan', 'Xochimilco'],
            'Coahuila' => ['Saltillo', 'Torreón', 'Monclova', 'Piedras Negras'],
            'Colima' => ['Colima', 'Manzanillo', 'Tecomán', 'Villa de Álvarez'],
            'Durango' => ['Durango', 'Gómez Palacio', 'Lerdo', 'Santiago Papasquiaro'],
            'Estado de México' => ['Toluca', 'Naucalpan', 'Tlalnepantla', 'Nezahualcóyotl', 'Ecatepec', 'Cuautitlán'],
            'Guanajuato' => ['León', 'Guanajuato', 'Irapuato', 'Celaya', 'Salamanca', 'Pénjamo'],
            'Guerrero' => ['Acapulco', 'Chilpancingo', 'Iguala', 'Taxco', 'Zihuatanejo'],
            'Hidalgo' => ['Pachuca', 'Tulancingo', 'Tizayuca', 'Huejutla'],
            'Jalisco' => ['Guadalajara', 'Zapopan', 'Tlaquepaque', 'Tonalá', 'Puerto Vallarta', 'Tlajomulco'],
            'Michoacán' => ['Morelia', 'Uruapan', 'Zamora', 'Lázaro Cárdenas', 'Apatzingán'],
            'Morelos' => ['Cuernavaca', 'Jiutepec', 'Temixco', 'Cuautla'],
            'Nayarit' => ['Tepic', 'Bahía de Banderas', 'Xalisco', 'Santiago Ixcuintla'],
            'Nuevo León' => ['Monterrey', 'Guadalupe', 'San Nicolás de los Garza', 'Apodaca', 'Santa Catarina'],
            'Oaxaca' => ['Oaxaca de Juárez', 'Salina Cruz', 'Tuxtepec', 'Juchitán'],
            'Puebla' => ['Puebla', 'Tehuacán', 'San Martín Texmelucan', 'Atlixco'],
            'Querétaro' => ['Santiago de Querétaro', 'San Juan del Río', 'Corregidora', 'El Marqués'],
            'Quintana Roo' => ['Cancún', 'Chetumal', 'Playa del Carmen', 'Cozumel', 'Tulum'],
            'San Luis Potosí' => ['San Luis Potosí', 'Soledad de Graciano Sánchez', 'Ciudad Valles', 'Matehuala'],
            'Sinaloa' => ['Culiacán', 'Mazatlán', 'Los Mochis', 'Guasave'],
            'Sonora' => ['Hermosillo', 'Ciudad Obregón', 'Nogales', 'Navojoa'],
            'Tabasco' => ['Villahermosa', 'Cárdenas', 'Comalcalco', 'Huimanguillo'],
            'Tamaulipas' => ['Reynosa', 'Matamoros', 'Nuevo Laredo', 'Tampico', 'Ciudad Victoria'],
            'Tlaxcala' => ['Tlaxcala', 'Apizaco', 'Huamantla', 'Zacatelco'],
            'Veracruz' => ['Veracruz', 'Xalapa', 'Coatzacoalcos', 'Córdoba', 'Orizaba', 'Poza Rica'],
            'Yucatán' => ['Mérida', 'Valladolid', 'Progreso', 'Tizimín'],
            'Zacatecas' => ['Zacatecas', 'Fresnillo', 'Guadalupe', 'Jerez']
        ];

        $cities = $citiesByState[$state] ?? ['Ciudad Ejemplo'];
        return fake()->randomElement($cities);
    }

    /**
     * Get a realistic Mexican zip code based on state.
     */
    private function getMexicanZipCode(string $state): string
    {
        // Mexican postal codes by state (first 2 digits represent the state)
        $statePostalCodes = [
            'Aguascalientes' => '20',
            'Baja California' => '21',
            'Baja California Sur' => '23',
            'Campeche' => '24',
            'Chiapas' => '29',
            'Chihuahua' => '31',
            'Ciudad de México' => random_int(0, 1) ? '0' . random_int(1, 9) : random_int(10, 16),
            'Coahuila' => '25',
            'Colima' => '28',
            'Durango' => '34',
            'Estado de México' => random_int(50, 57),
            'Guanajuato' => '36',
            'Guerrero' => '39',
            'Hidalgo' => '42',
            'Jalisco' => random_int(44, 49),
            'Michoacán' => random_int(58, 61),
            'Morelos' => '62',
            'Nayarit' => '63',
            'Nuevo León' => random_int(64, 67),
            'Oaxaca' => random_int(68, 71),
            'Puebla' => random_int(72, 75),
            'Querétaro' => '76',
            'Quintana Roo' => '77',
            'San Luis Potosí' => '78',
            'Sinaloa' => random_int(80, 82),
            'Sonora' => '83',
            'Tabasco' => '86',
            'Tamaulipas' => random_int(87, 89),
            'Tlaxcala' => '90',
            'Veracruz' => random_int(91, 96),
            'Yucatán' => '97',
            'Zacatecas' => '98'
        ];

        $stateCode = $statePostalCodes[$state] ?? '99';
        
        // Handle special cases for states with ranges
        if (is_numeric($stateCode) && $stateCode < 10) {
            $stateCode = str_pad($stateCode, 2, '0', STR_PAD_LEFT);
        }
        
        $countryPrefix = config('app.country.postal_code_prefix', 'MX-');
        $lastThreeDigits = str_pad(fake()->numberBetween(0, 999), 3, '0', STR_PAD_LEFT);
        
        return $countryPrefix . $stateCode . $lastThreeDigits;
    }

    /**
     * Get a random Mexican street address.
     */
    private function getMexicanStreetAddress(): string
    {
        $streetTypes = ['Calle', 'Avenida', 'Boulevard', 'Privada', 'Cerrada', 'Callejón'];
        $streetNames = [
            'Benito Juárez', 'Miguel Hidalgo', 'Francisco I. Madero', 'Emiliano Zapata',
            'Morelos', 'Insurgentes', 'Reforma', 'Revolución', '16 de Septiembre',
            'Independencia', 'Constitución', 'Libertad', 'Progreso', 'Juárez',
            'Las Flores', 'Los Pinos', 'del Sol', 'de la Paz', 'Principal',
            'Centro', 'Norte', 'Sur', 'Oriente', 'Poniente'
        ];

        $streetType = fake()->randomElement($streetTypes);
        $streetName = fake()->randomElement($streetNames);
        $number = fake()->numberBetween(1, 9999);
        
        return "{$streetType} {$streetName} #{$number}";
    }
}
