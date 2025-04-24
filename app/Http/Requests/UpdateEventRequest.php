<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after_or_equal:start_time',
            'location' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Etkinlik başlığı gereklidir.',
            'title.max' => 'Etkinlik başlığı en fazla 255 karakter olabilir.',
            'start_time.required' => 'Başlangıç zamanı gereklidir.',
            'start_time.date' => 'Başlangıç zamanı geçerli bir tarih olmalıdır.',
            'end_time.required' => 'Bitiş zamanı gereklidir.',
            'end_time.date' => 'Bitiş zamanı geçerli bir tarih olmalıdır.',
            'end_time.after_or_equal' => 'Bitiş zamanı başlangıç zamanından sonra veya aynı olmalıdır.',
            'location.max' => 'Konum en fazla 255 karakter olabilir.',
        ];
    }
} 