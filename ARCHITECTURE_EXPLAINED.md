# Laravel API Architecture Explanation

## How the 4 Components Work Together

### 1. **Resources** - Clean API Responses

**Purpose**: Transform raw database data into consistent JSON

**Example Raw Data** (what comes from database):
```php
Property {
  id: 1,
  title: "Beautiful Home",
  price: 250000.00,
  square_feet: 1500,
  created_at: "2025-11-01 21:05:51",
  user_id: 1,
  user: User { name: "John Doe", email: "john@example.com" }
}
```

**PropertyResource transforms it to:**
```json
{
  "id": 1,
  "title": "Beautiful Home",
  "property_details": {
    "square_feet": 1500
  },
  "financial": {
    "price": "250000.00",
    "price_per_sqft": "166.67"
  },
  "owner": {
    "name": "John Doe"
  },
  "created_at": "2025-11-01T21:05:51.000Z"
}
```

**Benefits:**
- Consistent structure across all endpoints
- Hide sensitive data (email not exposed)
- Calculated fields (price_per_sqft)
- Grouped related fields
- Proper date formatting

---

### 2. **Form Requests** - Validation & Authorization

**Purpose**: Handle validation and security before reaching controller

**Flow:**
```
1. API Request → 2. Form Request → 3. Controller → 4. Resource → 5. JSON Response
                     ↓
               Validates & Authorizes
```

**StorePropertyRequest Example:**
```php
public function rules(): array
{
    return [
        'square_feet' => 'required|integer|min:1',
        'price' => 'nullable|numeric|min:0',
        'property_type' => 'in:house,condo,apartment'
    ];
}

public function prepareForValidation(): void
{
    // Auto-calculate price per sqft
    if ($this->price && $this->square_feet) {
        $this->merge([
            'price_per_sqft' => $this->price / $this->square_feet
        ]);
    }
}
```

**What happens:**
- Bad data? → Returns 422 error BEFORE hitting controller
- Unauthorized? → Returns 403 error BEFORE hitting controller
- Good data? → Passes clean, validated data to controller

---

### 3. **API Directory Structure** - Organization

**Purpose**: Separate API logic from web logic

```
app/Http/Controllers/
├── Api/PropertyController.php    ← Returns JSON, uses 'api' middleware
└── PropertyController.php        ← Could return HTML, uses 'web' middleware

routes/
├── api.php                       ← Your property API routes
└── web.php                       ← Web page routes
```

**API Controller vs Web Controller:**
```php
// Api/PropertyController.php
public function index(): PropertyCollection
{
    return new PropertyCollection(Property::paginate());
}

// PropertyController.php (if you had web views)
public function index()
{
    return view('properties.index', [
        'properties' => Property::paginate()
    ]);
}
```

---

### 4. **Database Indexes** - Performance

**Purpose**: Speed up common queries

**Your Migration:**
```php
// These make filtering FAST
$table->index(['city', 'state']);           // For: WHERE city='Chicago' AND state='IL'
$table->index(['property_type', 'status']); // For: WHERE type='house' AND status='available'
$table->index(['bedrooms', 'bathrooms']);   // For: WHERE bedrooms=3 AND bathrooms=2
$table->index(['price', 'square_feet']);    // For: ORDER BY price, WHERE price BETWEEN
```

**Performance Impact:**
```php
// Without indexes (SLOW - scans every row)
Property::where('city', 'Chicago')->where('bedrooms', 3)->get();
// Time: 2000ms for 100k records

// With indexes (FAST - uses index lookup)
Property::where('city', 'Chicago')->where('bedrooms', 3)->get();
// Time: 5ms for 100k records
```

**Index Types:**
- **Single**: `$table->index('city')` - Good for `WHERE city = 'Chicago'`
- **Composite**: `$table->index(['city', 'state'])` - Good for `WHERE city = 'Chicago' AND state = 'IL'`
- **Order matters**: `['city', 'state']` is different from `['state', 'city']`

---

## Real-World Example Flow

**1. API Request:**
```bash
POST /api/properties
{
  "title": "Beach House",
  "square_feet": 2000,
  "price": 400000,
  "property_type": "house"
}
```

**2. Form Request (StorePropertyRequest):**
- ✅ Validates: square_feet is integer, price is numeric
- ✅ Authorizes: User can create properties
- ✅ Prepares: Calculates price_per_sqft = 400000/2000 = 200

**3. Controller:**
```php
public function store(StorePropertyRequest $request): JsonResponse
{
    $property = Property::create($request->validated()); // Clean data
    return (new PropertyResource($property))->response()->setStatusCode(201);
}
```

**4. Database:**
- Uses indexes to quickly insert
- Automatically sets created_at timestamp

**5. Resource (PropertyResource):**
```php
return [
    'id' => $this->id,
    'title' => $this->title,
    'property_details' => [
        'square_feet' => $this->square_feet,
    ],
    'financial' => [
        'price' => $this->price,
        'price_per_sqft' => $this->price_per_sqft, // Auto-calculated!
    ],
    'created_at' => $this->created_at->toISOString(),
];
```

**6. JSON Response:**
```json
{
  "data": {
    "id": 47,
    "title": "Beach House",
    "property_details": {
      "square_feet": 2000
    },
    "financial": {
      "price": "400000.00",
      "price_per_sqft": "200.00"
    },
    "created_at": "2025-11-01T21:05:51.000Z"
  }
}
```

---

## Why This Architecture Scales

1. **Resources**: Change response format once, affects all endpoints
2. **Form Requests**: Reuse validation across create/update endpoints
3. **API Structure**: Add v2 API without touching v1
4. **Indexes**: Handle millions of properties with same performance

This setup handles everything from 10 properties to 10 million properties! 🏠🚀