<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePropertyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->role === 'owner' || $this->user()->role === 'agent';
    }

    public function rules(): array
    {
        return [
            'title'            => ['required', 'string', 'max:150'],
            'description'      => ['nullable', 'string', 'max:5000'],
            'property_type'    => ['required', Rule::in(['apartment', 'house', 'villa', 'office', 'land', 'commercial', 'warehouse'])],
            'listing_type'     => ['required', Rule::in(['rent', 'sale'])],
            'price'            => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'price_negotiable' => ['nullable', 'boolean'],
            'bedrooms'         => ['nullable', 'integer', 'min:0', 'max:50'],
            'bathrooms'        => ['nullable', 'integer', 'min:0', 'max:50'],
            'parking_spaces'   => ['nullable', 'integer', 'min:0', 'max:50'],
            'furnishing_status' => ['nullable', Rule::in(['unfurnished', 'semi_furnished', 'furnished'])],
            'area'             => ['nullable', 'numeric', 'min:0'],
            'city'             => ['required', 'string', 'max:100'],
            'sub_city'         => ['nullable', 'string', 'max:100'],
            'woreda'           => ['nullable', 'string', 'max:100'],
            'neighborhood'     => ['nullable', 'string', 'max:150'],
            'address'          => ['nullable', 'string', 'max:500'],
            'latitude'         => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'        => ['nullable', 'numeric', 'between:-180,180'],
            'status' => ['required', 'in:draft,published'],
            'photos'           => ['nullable', 'array', 'max:20'],
            'media_ids'       => ['nullable', 'array', 'max:20'],
            'media_ids.*'     => ['integer', 'exists:media,id'],
            'cover_media_id'  => ['nullable', 'integer', 'exists:media,id'],
        ];
    }
}
