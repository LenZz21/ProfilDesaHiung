<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

class StoreComplaintSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'whatsapp' => ['required', 'string', 'max:25', 'regex:/^(\+62|62|0)[0-9]{8,15}$/'],
            'complaint' => ['required', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'whatsapp.regex' => __('Nomor WhatsApp tidak valid.'),
        ];
    }
}
