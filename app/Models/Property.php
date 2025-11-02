<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Property extends Model
{
    /** @use HasFactory<\Database\Factories\PropertyFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'description',
        'address',
        'city',
        'state',
        'zip_code',
        'latitude',
        'longitude',
        'square_feet',
        'bedrooms',
        'bathrooms',
        'floors',
        'price',
        'price_per_sqft',
        'monthly_rent',
        'property_taxes',
        'property_type',
        'status',
        'year_built',
        'lot_size',
        'garage_spaces',
        'has_basement',
        'has_pool',
        'has_garden',
        'features',
        'metadata',
        'user_id',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'price' => 'decimal:2',
            'price_per_sqft' => 'decimal:2',
            'monthly_rent' => 'decimal:2',
            'property_taxes' => 'decimal:2',
            'lot_size' => 'decimal:2',
            'has_basement' => 'boolean',
            'has_pool' => 'boolean',
            'has_garden' => 'boolean',
            'features' => 'array',
            'metadata' => 'array',
            'year_built' => 'integer',
        ];
    }

    /**
     * Get the owner of the property.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculate and update price per square foot.
     */
    public function calculatePricePerSqft(): void
    {
        if ($this->price && $this->square_feet && $this->square_feet > 0) {
            $this->price_per_sqft = $this->price / $this->square_feet;
            $this->save();
        }
    }

    /**
     * Scope to filter by property type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('property_type', $type);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by minimum bedroom count.
     */
    public function scopeWithBedrooms($query, int $bedrooms)
    {
        return $query->where('bedrooms', '>=', $bedrooms);
    }

    /**
     * Scope to filter by minimum bathroom count.
     */
    public function scopeWithBathrooms($query, int $bathrooms)
    {
        return $query->where('bathrooms', '>=', $bathrooms);
    }

    /**
     * Scope to filter by price range.
     */
    public function scopeInPriceRange($query, ?float $minPrice = null, ?float $maxPrice = null)
    {
        if ($minPrice) {
            $query->where('price', '>=', $minPrice);
        }

        if ($maxPrice) {
            $query->where('price', '<=', $maxPrice);
        }

        return $query;
    }

    /**
     * Scope to filter by city.
     */
    public function scopeInCity($query, string $city)
    {
        return $query->where('city', 'like', "%{$city}%");
    }

    /**
     * Get formatted address.
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->zip_code,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get formatted zip code with country prefix.
     */
    public function getFormattedZipCodeAttribute(): string
    {
        if (! $this->zip_code) {
            return '';
        }

        // If zip code already has prefix, return as-is
        if (str_contains($this->zip_code, '-')) {
            return $this->zip_code;
        }

        // Add country prefix from config
        $prefix = config('app.country.postal_code_prefix', 'MX-');
        return $prefix . $this->zip_code;
    }

    /**
     * Check if property has specific feature.
     */
    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->features ?? []);
    }

    /**
     * Add a feature to the property.
     */
    public function addFeature(string $feature): void
    {
        $features = $this->features ?? [];

        if (! in_array($feature, $features)) {
            $features[] = $feature;
            $this->features = $features;
            $this->save();
        }
    }

    /**
     * Remove a feature from the property.
     */
    public function removeFeature(string $feature): void
    {
        $features = $this->features ?? [];

        if (($key = array_search($feature, $features)) !== false) {
            unset($features[$key]);
            $this->features = array_values($features);
            $this->save();
        }
    }
}
