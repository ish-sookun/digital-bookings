<?php

namespace App\Http\Requests;

use App\PlacementType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlacementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', Rule::enum(PlacementType::class)],
            'price' => ['required', 'integer', 'min:0'],
            'platform_id' => ['nullable', 'exists:platforms,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The placement name is required.',
            'type.required' => 'The placement type is required.',
            'type.enum' => 'The placement type must be either Web or Social Media.',
            'price.required' => 'The price is required.',
            'price.integer' => 'The price must be a valid number.',
            'price.min' => 'The price cannot be negative.',
            'platform_id.exists' => 'The selected platform is invalid.',
        ];
    }
}
