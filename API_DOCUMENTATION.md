# Property API Documentation

This API provides comprehensive management of real estate properties with SQLite storage.

## Base URL
```
/api/properties
```

## Endpoints

### 1. List Properties
**GET** `/api/properties`

Query Parameters:
- `city` - Filter by city name (partial match)
- `property_type` - Filter by type (casa, condominio, departamento, townhouse, duplex)
- `status` - Filter by status (disponible, pendiente, vendida, rentada)
- `bedrooms` - Filter by minimum number of bedrooms (e.g., 3 returns 3+ bedroom properties)
- `bathrooms` - Filter by minimum number of bathrooms (e.g., 2 returns 2+ bathroom properties)
- `min_price` - Minimum price filter
- `max_price` - Maximum price filter
- `sort_by` - Sort field (created_at, price, square_feet, bedrooms, bathrooms)
- `sort_direction` - Sort direction (asc, desc)
- `per_page` - Items per page (default: 15)

Example:
```bash
curl "http://localhost:8000/api/properties?city=Guadalajara&bedrooms=3&min_price=200000&max_price=500000"
```

### 2. Create Property
**POST** `/api/properties`

Request Body:
```json
{
  "title": "Beautiful Family Home",
  "description": "A lovely 3-bedroom house in a quiet neighborhood",
  "address": "123 Main St",
  "city": "Springfield",
  "state": "IL",
  "zip_code": "62701",
  "square_feet": 1500,
  "bedrooms": 3,
  "bathrooms": 2,
  "floors": 2,
  "price": 250000,
  "property_type": "house",
  "status": "available",
  "year_built": 2000,
  "has_basement": true,
  "has_pool": false,
  "has_garden": true,
  "features": ["hardwood_floors", "granite_countertops", "fireplace"],
  "metadata": {
    "mls_number": "12345678",
    "listing_agent": "John Doe"
  }
}
```

### 3. Get Single Property
**GET** `/api/properties/{id}`

Example:
```bash
curl "http://localhost:8000/api/properties/1"
```

### 4. Update Property
**PUT/PATCH** `/api/properties/{id}`

Request Body (partial update supported):
```json
{
  "price": 275000,
  "status": "pending",
  "features": ["hardwood_floors", "granite_countertops", "fireplace", "updated_kitchen"]
}
```

### 5. Delete Property
**DELETE** `/api/properties/{id}`

### 6. Search Properties
**GET** `/api/properties/search`

Query Parameters:
- `q` - Search term (searches title, description, address, city)
- `latitude` - Latitude for geographic search
- `longitude` - Longitude for geographic search  
- `radius` - Search radius in kilometers (default: 10)

Example:
```bash
curl "http://localhost:8000/api/properties/search?q=beautiful&latitude=41.8781&longitude=-87.6298&radius=5"
```

### 7. Get Statistics
**GET** `/api/properties/stats`

Returns summary statistics about all properties.

## Response Format

### Single Property Response
```json
{
  "data": {
    "id": 1,
    "title": "Beautiful Family Home",
    "description": "A lovely 3-bedroom house...",
    "address": {
      "street": "123 Main St",
      "city": "Springfield",
      "state": "IL",
      "zip_code": "62701",
      "full_address": "123 Main St, Springfield, IL, 62701"
    },
    "coordinates": {
      "latitude": "39.7817",
      "longitude": "-89.6501"
    },
    "property_details": {
      "square_feet": 1500,
      "bedrooms": 3,
      "bathrooms": 2,
      "floors": 2,
      "property_type": "house",
      "year_built": 2000,
      "lot_size": "0.25",
      "garage_spaces": 2
    },
    "amenities": {
      "has_basement": true,
      "has_pool": false,
      "has_garden": true,
      "features": ["hardwood_floors", "granite_countertops"]
    },
    "financial": {
      "price": "250000.00",
      "price_per_sqft": "166.67",
      "monthly_rent": null,
      "property_taxes": "3500.00"
    },
    "status": "available",
    "metadata": {
      "mls_number": "12345678",
      "listing_agent": "John Doe"
    },
    "owner": {
      "id": 1,
      "name": "Property Owner",
      "email": "owner@example.com"
    },
    "created_at": "2025-11-01T21:05:51.000Z",
    "updated_at": "2025-11-01T21:05:51.000Z"
  }
}
```

### Collection Response
```json
{
  "data": [...], // Array of property objects
  "meta": {
    "total": 46,
    "filters": {
      "city": "Chicago",
      "min_price": "200000"
    }
  },
  "links": {
    "self": "http://localhost:8000/api/properties"
  }
}
```

## Property Model Features

### Scopes (for custom queries)
- `ofType($type)` - Filter by property type
- `withStatus($status)` - Filter by status
- `withBedrooms($count)` - Filter by bedroom count
- `withBathrooms($count)` - Filter by bathroom count
- `inPriceRange($min, $max)` - Filter by price range
- `inCity($city)` - Filter by city (partial match)

### Methods
- `calculatePricePerSqft()` - Auto-calculate price per square foot
- `hasFeature($feature)` - Check if property has specific feature
- `addFeature($feature)` - Add a feature to the property
- `removeFeature($feature)` - Remove a feature from the property
- `getFullAddressAttribute()` - Get formatted full address

### Relationships
- `user()` - BelongsTo relationship with User model

## Scalability Features

1. **JSON Fields**: `features` and `metadata` fields allow flexible attribute storage
2. **Database Indexes**: Optimized for common query patterns
3. **Validation**: Comprehensive validation with custom error messages
4. **Resource Classes**: Clean, consistent API responses
5. **Factory & Seeder**: Easy test data generation
6. **Comprehensive Tests**: Full test coverage for all endpoints

## Example Usage

Start the development server:
```bash
php artisan serve
```

Test the API:
```bash
# List all properties
curl http://localhost:8000/api/properties

# Create a new property
curl -X POST http://localhost:8000/api/properties \
  -H "Content-Type: application/json" \
  -d '{"title":"Test Property","square_feet":1200,"bedrooms":2,"bathrooms":1,"price":180000}'

# Search properties
curl "http://localhost:8000/api/properties/search?q=house"

# Get statistics
curl http://localhost:8000/api/properties/stats
```