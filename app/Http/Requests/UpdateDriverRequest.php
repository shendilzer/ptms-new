<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDriverRequest extends FormRequest
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
        $driverId = $this->route('driver')->id;

        return [
            'driver_fullname' => 'required|string|max:255',
            'driver_license_number' => 'required|string|max:50|unique:drivers,driver_license_number,' . $driverId,
            'expiration_date' => 'required|date|after:today',
            'driver_contact_number' => 'required|string|max:20',
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
            'driver_fullname.required' => 'The driver full name field is required.',
            'driver_fullname.string' => 'The driver full name must be a string.',
            'driver_fullname.max' => 'The driver full name may not be greater than 255 characters.',

            'driver_license_number.required' => 'The driver license number field is required.',
            'driver_license_number.string' => 'The driver license number must be a string.',
            'driver_license_number.max' => 'The driver license number may not be greater than 50 characters.',
            'driver_license_number.unique' => 'This driver license number is already registered.',

            'expiration_date.required' => 'The expiration date field is required.',
            'expiration_date.date' => 'Please enter a valid expiration date.',
            'expiration_date.after' => 'The expiration date must be a future date.',

            'driver_contact_number.required' => 'The driver contact number field is required.',
            'driver_contact_number.string' => 'The driver contact number must be a string.',
            'driver_contact_number.max' => 'The driver contact number may not be greater than 20 characters.',
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
            'driver_fullname' => 'driver full name',
            'driver_license_number' => 'driver license number',
            'expiration_date' => 'expiration date',
            'driver_contact_number' => 'driver contact number',
        ];
    }
}
