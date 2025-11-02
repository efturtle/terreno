<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePropertyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Add proper authorization logic as needed
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:50'],
            'zip_code' => ['nullable', 'string', 'max:20'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'square_feet' => ['nullable', 'integer', 'min:1'],
            'bedrooms' => ['nullable', 'integer', 'min:0'],
            'bathrooms' => ['nullable', 'integer', 'min:0'],
            'floors' => ['nullable', 'integer', 'min:1'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'monthly_rent' => ['nullable', 'numeric', 'min:0'],
            'property_taxes' => ['nullable', 'numeric', 'min:0'],
            'property_type' => ['nullable', 'string', 'in:casa,condominio,departamento,townhouse,duplex'],
            'status' => ['nullable', 'string', 'in:disponible,pendiente,vendida,rentada'],
            'year_built' => ['nullable', 'integer', 'min:1800', 'max:'.date('Y')],
            'lot_size' => ['nullable', 'numeric', 'min:0'],
            'garage_spaces' => ['nullable', 'integer', 'min:0'],
            'has_basement' => ['nullable', 'boolean'],
            'has_pool' => ['nullable', 'boolean'],
            'has_garden' => ['nullable', 'boolean'],
            'features' => ['nullable', 'array'],
            'features.*' => ['string', 'max:100'],
            'metadata' => ['nullable', 'array'],
            'user_id' => ['nullable', 'exists:users,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'latitude.between' => 'Latitude must be between -90 and 90 degrees.',
            'longitude.between' => 'Longitude must be between -180 and 180 degrees.',
            'square_feet.min' => 'Square feet must be at least 1.',
            'year_built.min' => 'Year built cannot be before 1800.',
            'year_built.max' => 'Year built cannot be in the future.',
            'property_type.in' => 'El tipo de propiedad debe ser: casa, condominio, departamento, townhouse o duplex.',
            'status.in' => 'El estatus debe ser: disponible, pendiente, vendida o rentada.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Auto-calculate price per sqft if both price and square_feet are provided
        if ($this->has('price') && $this->has('square_feet') && $this->square_feet > 0) {
            $this->merge([
                'price_per_sqft' => round($this->price / $this->square_feet, 2),
            ]);
        }
    }
}
