<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLetterSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $letterServiceKeys = array_column(config('public_services.letter_services', []), 'key');

        return [
            'service_type' => ['required', 'string', Rule::in($letterServiceKeys)],
            'full_name' => ['required', 'string', 'max:255'],
            'nik' => ['required', 'digits:16'],
            'whatsapp' => ['required', 'string', 'max:25', 'regex:/^(\+62|62|0)[0-9]{8,15}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'purpose' => ['required', 'string', 'max:3000'],
        ];
    }

    public function messages(): array
    {
        return [
            'service_type.required' => __('Silakan pilih jenis surat terlebih dahulu.'),
            'service_type.in' => __('Jenis surat yang dipilih tidak tersedia.'),
            'nik.digits' => __('NIK harus terdiri dari 16 digit angka.'),
            'whatsapp.regex' => __('Nomor WhatsApp tidak valid.'),
        ];
    }
}
