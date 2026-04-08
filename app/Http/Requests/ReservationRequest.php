<?php

namespace App\Http\Requests;

use App\ReservationStatus;
use App\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReservationRequest extends FormRequest
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
            'client_id' => ['required', 'exists:clients,id'],
            'agency_id' => ['nullable', 'exists:agencies,id'],
            'salesperson_id' => ['nullable', 'exists:salespeople,id'],
            'product' => ['required', 'string', 'max:255'],
            'platform_id' => ['nullable', 'exists:platforms,id'],
            'placement_id' => ['required', 'exists:placements,id'],
            'channel' => ['required', Rule::in(['Run of site', 'Home & multimedia'])],
            'scope' => ['required', Rule::in(['Mauritius only', 'Worldwide'])],
            'dates_booked' => ['required', 'json'],
            'gross_amount' => ['required', 'numeric', 'min:0'],
            'total_amount_to_pay' => ['required', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'commission' => ['nullable', 'numeric', 'min:0'],
            'cost_of_artwork' => ['nullable', 'numeric', 'min:0'],
            'vat' => ['nullable', 'numeric', 'min:0'],
            'vat_exempt' => ['boolean'],
            'status' => ['required', Rule::enum(ReservationStatus::class)],
            'purchase_order_no' => ['nullable', 'string', 'max:255'],
            'purchase_order_file' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif,webp', 'max:10240'],
            'invoice_no' => ['nullable', 'string', 'max:255'],
            'invoice_file' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif,webp', 'max:10240'],
            'signed_ro_file' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif,webp', 'max:10240'],
            'remark' => ['nullable', 'string'],
        ];
    }

    /**
     * Strip discount and commission fields for salesperson users.
     *
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null): mixed
    {
        $validated = parent::validated($key, $default);

        if ($key === null && $this->user()->role === UserRole::Salesperson) {
            unset($validated['discount'], $validated['commission']);
        }

        return $validated;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'client_id.required' => 'Please select a client.',
            'client_id.exists' => 'The selected client is invalid.',
            'product.required' => 'The product name is required.',
            'platform_id.exists' => 'The selected platform is invalid.',
            'placement_id.required' => 'Please select a placement.',
            'placement_id.exists' => 'The selected placement is invalid.',
            'channel.required' => 'Please select a channel.',
            'channel.in' => 'The selected channel is invalid.',
            'scope.required' => 'Please select a scope.',
            'scope.in' => 'The selected scope is invalid.',
            'dates_booked.required' => 'Please select at least one date.',
            'dates_booked.json' => 'The dates format is invalid.',
            'gross_amount.required' => 'The gross amount is required.',
            'gross_amount.numeric' => 'The gross amount must be a valid number.',
            'gross_amount.min' => 'The gross amount cannot be negative.',
            'total_amount_to_pay.required' => 'The total amount to pay is required.',
            'total_amount_to_pay.numeric' => 'The total amount to pay must be a valid number.',
            'total_amount_to_pay.min' => 'The total amount to pay cannot be negative.',
            'status.required' => 'Please select a reservation status.',
            'status.enum' => 'The selected status is invalid.',
        ];
    }
}
