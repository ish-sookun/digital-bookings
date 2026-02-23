<?php

namespace App\Http\Requests;

use App\CommissionType;
use App\DiscountType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AgencyRequest extends FormRequest
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
            'company_name' => ['required', 'string', 'max:255'],
            'company_logo' => ['nullable', 'file', 'mimes:jpeg,jpg,png', 'max:1024'],
            'brn' => ['required', 'string', 'max:255'],
            'vat_number' => ['nullable', 'integer'],
            'vat_exempt' => ['boolean'],
            'phone' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'commission_amount' => ['nullable', 'integer', 'min:0'],
            'commission_type' => ['nullable', Rule::enum(CommissionType::class)],
            'discount' => ['nullable', 'integer', 'min:0'],
            'discount_type' => ['nullable', Rule::enum(DiscountType::class)],
            'contact_person_name' => ['nullable', 'string', 'max:255'],
            'contact_person_email' => ['nullable', 'email', 'max:255'],
            'contact_person_phone' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'company_name.required' => 'The company name is required.',
            'brn.required' => 'The BRN is required.',
            'phone.required' => 'The phone number is required.',
            'address.required' => 'The address is required.',
            'contact_person_email.email' => 'The contact person email must be a valid email address.',
        ];
    }
}
