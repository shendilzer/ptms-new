<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOperatorRequest extends FormRequest
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
            'fullname' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'email_address' => 'required|email|unique:operators,email_address',
            'contact_number' => 'required|string|max:20',
            'driver_id' => 'required|exists:drivers,id',
            'motorcycle_id' => 'required|exists:motorcycles,id',
            'mtop_number' => 'required|string|max:50|unique:operators,mtop_number',
            'toda_id' => 'required|exists:toda,id',
            'date_registered' => 'required|date|before_or_equal:today',
            'date_last_paid' => 'nullable|date|before_or_equal:today',
            'permit_status' => 'required|in:new,renew,retire',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'fullname.required' => 'The full name field is required.',
            'fullname.string' => 'The full name must be a string.',
            'fullname.max' => 'The full name may not be greater than 255 characters.',

            'address.required' => 'The address field is required.',
            'address.string' => 'The address must be a string.',
            'address.max' => 'The address may not be greater than 500 characters.',

            'email_address.required' => 'The email address field is required.',
            'email_address.email' => 'Please enter a valid email address.',
            'email_address.unique' => 'This email address is already registered.',

            'contact_number.required' => 'The contact number field is required.',
            'contact_number.string' => 'The contact number must be a string.',
            'contact_number.max' => 'The contact number may not be greater than 20 characters.',

            'driver_id.required' => 'Please select a driver.',
            'driver_id.exists' => 'The selected driver does not exist.',

            'motorcycle_id.required' => 'Please select a motorcycle.',
            'motorcycle_id.exists' => 'The selected motorcycle does not exist.',

            'mtop_number.required' => 'The MTOP number field is required.',
            'mtop_number.string' => 'The MTOP number must be a string.',
            'mtop_number.max' => 'The MTOP number may not be greater than 50 characters.',
            'mtop_number.unique' => 'This MTOP number is already registered.',

            'toda_id.required' => 'Please select a TODA.',
            'toda_id.exists' => 'The selected TODA does not exist.',

            'date_registered.required' => 'The date registered field is required.',
            'date_registered.date' => 'Please enter a valid date.',
            'date_registered.before_or_equal' => 'The date registered cannot be in the future.',

            'date_last_paid.date' => 'Please enter a valid date.',
            'date_last_paid.before_or_equal' => 'The date last paid cannot be in the future.',

            'permit_status.required' => 'The permit status field is required.',
            'permit_status.in' => 'The permit status must be either new, renew, or retire.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'fullname' => 'full name',
            'email_address' => 'email address',
            'contact_number' => 'contact number',
            'driver_id' => 'driver',
            'motorcycle_id' => 'motorcycle',
            'mtop_number' => 'MTOP number',
            'toda_id' => 'TODA',
            'date_registered' => 'date registered',
            'date_last_paid' => 'date last paid',
            'permit_status' => 'permit status',
        ];
    }
}
