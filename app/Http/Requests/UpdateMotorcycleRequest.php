<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMotorcycleRequest extends FormRequest
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
        $motorcycleId = $this->route('motorcycle')->id;

        return [
            'plate_number' => 'required|string|max:20|unique:motorcycles,plate_number,' . $motorcycleId,
            'motor_number' => 'required|string|max:100',
            'chassis_number' => 'required|string|max:100',
            'make' => 'required|string|max:100',
            'year_model' => 'required|string|max:10',
            'registered_date' => 'required|date|before_or_equal:today',
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
            'plate_number.required' => 'The plate number field is required.',
            'plate_number.string' => 'The plate number must be a string.',
            'plate_number.max' => 'The plate number may not be greater than 20 characters.',
            'plate_number.unique' => 'This plate number is already registered.',

            'motor_number.required' => 'The motor number field is required.',
            'motor_number.string' => 'The motor number must be a string.',
            'motor_number.max' => 'The motor number may not be greater than 100 characters.',

            'chassis_number.required' => 'The chassis number field is required.',
            'chassis_number.string' => 'The chassis number must be a string.',
            'chassis_number.max' => 'The chassis number may not be greater than 100 characters.',

            'make.required' => 'The make field is required.',
            'make.string' => 'The make must be a string.',
            'make.max' => 'The make may not be greater than 100 characters.',

            'year_model.required' => 'The year model field is required.',
            'year_model.string' => 'The year model must be a string.',
            'year_model.max' => 'The year model may not be greater than 10 characters.',

            'registered_date.required' => 'The registered date field is required.',
            'registered_date.date' => 'Please enter a valid registered date.',
            'registered_date.before_or_equal' => 'The registered date cannot be in the future.',
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
            'plate_number' => 'plate number',
            'motor_number' => 'motor number',
            'chassis_number' => 'chassis number',
            'year_model' => 'year model',
            'registered_date' => 'registered date',
        ];
    }
}
