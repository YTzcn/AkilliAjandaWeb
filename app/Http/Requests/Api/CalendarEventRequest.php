<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CalendarEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'all_day' => ['boolean'],
            'location' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Etkinlik başlığı gereklidir.',
            'title.max' => 'Etkinlik başlığı en fazla 255 karakter olabilir.',
            'start_date.required' => 'Başlangıç tarihi gereklidir.',
            'start_date.date' => 'Geçerli bir başlangıç tarihi giriniz.',
            'end_date.required' => 'Bitiş tarihi gereklidir.',
            'end_date.date' => 'Geçerli bir bitiş tarihi giriniz.',
            'end_date.after_or_equal' => 'Bitiş tarihi başlangıç tarihinden önce olamaz.',
            'location.max' => 'Konum en fazla 255 karakter olabilir.',
        ];
    }
} 