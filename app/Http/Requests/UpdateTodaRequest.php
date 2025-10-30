<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTodaRequest extends FormRequest
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
        $todaId = $this->route('toda')->id;

        return [
            'toda_name' => 'required|string|max:255|unique:toda,toda_name,' . $todaId,
            'toda_president' => 'required|string|max:255',
            'date_registered' => 'required|date|before_or_equal:today',
            'toda_status' => 'required|in:active,inactive',
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
            'toda_name.required' => 'The TODA name field is required.',
            'toda_name.string' => 'The TODA name must be a string.',
            'toda_name.max' => 'The TODA name may not be greater than 255 characters.',
            'toda_name.unique' => 'This TODA name is already registered.',

            'toda_president.required' => 'The TODA president field is required.',
            'toda_president.string' => 'The TODA president must be a string.',
            'toda_president.max' => 'The TODA president may not be greater than 255 characters.',

            'date_registered.required' => 'The date registered field is required.',
            'date_registered.date' => 'Please enter a valid date.',
            'date_registered.before_or_equal' => 'The date registered cannot be in the future.',

            'toda_status.required' => 'The TODA status field is required.',
            'toda_status.in' => 'The TODA status must be either active or inactive.',
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
            'toda_name' => 'TODA name',
            'toda_president' => 'TODA president',
            'date_registered' => 'date registered',
            'toda_status' => 'TODA status',
        ];
    }
}
