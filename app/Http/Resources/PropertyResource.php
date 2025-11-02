<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'address' => [
                'street' => $this->address,
                'city' => $this->city,
                'state' => $this->state,
                'zip_code' => $this->zip_code,
                'full_address' => $this->full_address,
            ],
            'coordinates' => [
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
            ],
            'property_details' => [
                'square_feet' => $this->square_feet,
                'bedrooms' => $this->bedrooms,
                'bathrooms' => $this->bathrooms,
                'floors' => $this->floors,
                'property_type' => $this->property_type,
                'year_built' => $this->year_built,
                'lot_size' => $this->lot_size,
                'garage_spaces' => $this->garage_spaces,
            ],
            'amenities' => [
                'has_basement' => $this->has_basement,
                'has_pool' => $this->has_pool,
                'has_garden' => $this->has_garden,
                'features' => $this->features ?? [],
            ],
            'financial' => [
                'price' => $this->price,
                'price_per_sqft' => $this->price_per_sqft,
                'monthly_rent' => $this->monthly_rent,
                'property_taxes' => $this->property_taxes,
            ],
            'status' => $this->status,
            'metadata' => $this->metadata ?? [],
            'owner' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
