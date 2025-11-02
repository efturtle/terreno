<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePropertyRequest;
use App\Http\Requests\UpdatePropertyRequest;
use App\Http\Resources\PropertyCollection;
use App\Http\Resources\PropertyResource;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PropertyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): PropertyCollection
    {
        $query = Property::query();

        // Apply filters
        if ($request->filled('city')) {
            $query->inCity($request->input('city'));
        }

        if ($request->filled('property_type')) {
            $query->ofType($request->input('property_type'));
        }

        if ($request->filled('status')) {
            $query->withStatus($request->input('status'));
        }

        if ($request->filled('bedrooms')) {
            $query->withBedrooms($request->integer('bedrooms'));
        }

        if ($request->filled('bathrooms')) {
            $query->withBathrooms($request->integer('bathrooms'));
        }

        if ($request->filled('min_price') || $request->filled('max_price')) {
            $query->inPriceRange(
                $request->input('min_price'),
                $request->input('max_price')
            );
        }

        // Apply sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');

        $allowedSorts = ['created_at', 'updated_at', 'price', 'square_feet', 'bedrooms', 'bathrooms'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDirection);
        }

        // Load relationships
        $query->with('user');

        // Paginate results
        $properties = $query->paginate($request->integer('per_page', 15));

        return new PropertyCollection($properties);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePropertyRequest $request): JsonResponse
    {
        $property = Property::create($request->validated());

        // Calculate price per sqft if applicable
        if ($property->price && $property->square_feet) {
            $property->calculatePricePerSqft();
        }

        $property->load('user');

        return (new PropertyResource($property))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Property $property): PropertyResource
    {
        $property->load('user');

        return new PropertyResource($property);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePropertyRequest $request, Property $property): PropertyResource
    {
        $property->update($request->validated());

        // Recalculate price per sqft if price or square_feet changed
        if ($request->has(['price', 'square_feet']) && $property->price && $property->square_feet) {
            $property->calculatePricePerSqft();
        }

        $property->load('user');

        return new PropertyResource($property);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Property $property): JsonResponse
    {
        $property->delete();

        return response()->json([
            'message' => 'Property deleted successfully',
        ], Response::HTTP_NO_CONTENT);
    }

    /**
     * Search properties by various criteria.
     */
    public function search(Request $request): PropertyCollection
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'radius' => ['nullable', 'numeric', 'min:0.1', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $query = Property::query();

        // Text search
        if ($request->filled('q')) {
            $searchTerm = $request->input('q');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%")
                    ->orWhere('address', 'like', "%{$searchTerm}%")
                    ->orWhere('city', 'like', "%{$searchTerm}%");
            });
        }

        // Geographic search (basic implementation)
        if ($request->filled(['latitude', 'longitude', 'radius'])) {
            $lat = $request->input('latitude');
            $lng = $request->input('longitude');
            $radius = $request->input('radius', 10); // Default 10km

            // Simple bounding box search (for more accurate results, use spatial database functions)
            $latDelta = $radius / 111; // Approximate km per degree
            $lngDelta = $radius / (111 * cos(deg2rad($lat)));

            $query->whereBetween('latitude', [$lat - $latDelta, $lat + $latDelta])
                ->whereBetween('longitude', [$lng - $lngDelta, $lng + $lngDelta]);
        }

        $properties = $query->with('user')->paginate($request->integer('per_page', 15));

        return new PropertyCollection($properties);
    }

    /**
     * Get property statistics.
     */
    public function stats(): JsonResponse
    {
        $stats = [
            'total_properties' => Property::count(),
            'available_properties' => Property::withStatus('available')->count(),
            'sold_properties' => Property::withStatus('sold')->count(),
            'rented_properties' => Property::withStatus('rented')->count(),
            'average_price' => Property::whereNotNull('price')->avg('price'),
            'average_price_per_sqft' => Property::whereNotNull('price_per_sqft')->avg('price_per_sqft'),
            'average_square_feet' => Property::whereNotNull('square_feet')->avg('square_feet'),
            'property_types' => Property::selectRaw('property_type, COUNT(*) as count')
                ->whereNotNull('property_type')
                ->groupBy('property_type')
                ->get()
                ->pluck('count', 'property_type'),
        ];

        return response()->json([
            'data' => $stats,
        ]);
    }
}
