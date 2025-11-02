# Mexican Address Generation for Property Factory

## Overview
Your PropertyFactory now generates realistic Mexican addresses instead of generic Faker data. This provides more authentic test data for your real estate application.

## Features Implemented

### 1. **Mexican States** (32 states + CDMX)
Complete list of all Mexican states including:
- Aguascalientes, Baja California, Baja California Sur, Campeche
- Chiapas, Chihuahua, Ciudad de México, Coahuila, Colima
- And all 32 states...

### 2. **State-Specific Cities**
Each state has its own realistic cities:
```php
'Jalisco' => ['Guadalajara', 'Zapopan', 'Tlaquepaque', 'Tonalá', 'Puerto Vallarta', 'Tlajomulco']
'Nuevo León' => ['Monterrey', 'Guadalupe', 'San Nicolás de los Garza', 'Apodaca', 'Santa Catarina']
'Quintana Roo' => ['Cancún', 'Chetumal', 'Playa del Carmen', 'Cozumel', 'Tulum']
```

### 3. **Realistic Postal Codes**
Mexican postal codes follow the official format:
- **Format**: `MX-{state_code}{3_digits}`
- **Examples**: 
  - `MX-44123` (Jalisco - Guadalajara area)
  - `MX-64000` (Nuevo León - Monterrey area)
  - `MX-77500` (Quintana Roo - Cancún area)

### 4. **Mexican Street Addresses**
Authentic Mexican street naming:
```php
// Examples generated:
"Calle Benito Juárez #1234"
"Avenida Insurgentes #567" 
"Boulevard Revolución #89"
"Privada Miguel Hidalgo #2345"
```

### 5. **Localized Property Data**
- **Property Types**: `casa`, `condominio`, `departamento`, `townhouse`, `duplex`
- **Status Values**: `disponible`, `pendiente`, `vendida`, `rentada`
- **Coordinates**: Within Mexico's geographic bounds (14.5°-32.7°N, -118.4°--86.7°W)

## Usage Examples

### Basic Property Generation
```php
$property = Property::factory()->create();
// Generates random Mexican state with matching city and zip code
```

### Specific State
```php
$property = Property::factory()->inState('Jalisco')->create();
// Generates property in Jalisco with Jalisco cities and zip codes
```

### Multiple Properties in Different States
```php
$properties = collect(['Jalisco', 'Nuevo León', 'Quintana Roo'])
    ->map(fn($state) => Property::factory()->inState($state)->create());
```

## Sample Generated Data

```json
{
  "title": "Casa moderna en excelente ubicación",
  "address": "Calle Miguel Hidalgo #1847",
  "city": "Guadalajara", 
  "state": "Jalisco",
  "zip_code": "MX-44123",
  "latitude": 20.6596,
  "longitude": -103.3496,
  "property_type": "casa",
  "status": "disponible"
}
```

## Validation Updates

Form requests now validate Spanish property types and statuses:
```php
// StorePropertyRequest & UpdatePropertyRequest
'property_type' => 'in:casa,condominio,departamento,townhouse,duplex'
'status' => 'in:disponible,pendiente,vendida,rentada'
```

Error messages are also in Spanish:
```php
'property_type.in' => 'El tipo de propiedad debe ser: casa, condominio, departamento, townhouse o duplex.'
'status.in' => 'El estatus debe ser: disponible, pendiente, vendida o rentada.'
```

## Benefits

1. **Realistic Test Data**: Properties now have authentic Mexican addresses
2. **Geographic Consistency**: Cities match their states, zip codes match regions
3. **Localization**: All text is appropriate for Mexican market
4. **Scalability**: Easy to add more cities or modify regions
5. **Testability**: Comprehensive tests ensure data integrity

## Configuration

Controlled via environment variables:
```env
APP_COUNTRY_CODE=MX
APP_POSTAL_PREFIX=MX-
APP_CURRENCY=MXN
```

Your property API now generates test data that looks and feels like real Mexican real estate listings! 🏠🇲🇽