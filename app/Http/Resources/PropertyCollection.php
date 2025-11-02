<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PropertyCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => PropertyResource::collection($this->collection),
            'meta' => [
                'total' => $this->collection->count(),
                'filters' => $request->only(['city', 'property_type', 'status', 'min_price', 'max_price', 'bedrooms', 'bathrooms']),
            ],
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     */
    public function with(Request $request): array
    {
        return [
            'links' => [
                'self' => route('api.properties.index'),
            ],
        ];
    }
}
