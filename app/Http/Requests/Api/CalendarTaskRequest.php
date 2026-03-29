<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CalendarTaskRequest extends FormRequest
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
            'due_date' => ['required', 'date'],
            'priority' => ['required', 'integer', 'min:1', 'max:3'],
            'status' => ['required', 'string', 'in:pending,completed'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => [
                'integer',
                Rule::exists('categories', 'id')->where('user_id', auth()->id()),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Görev başlığı gereklidir.',
            'title.max' => 'Görev başlığı en fazla 255 karakter olabilir.',
            'due_date.required' => 'Son tarih gereklidir.',
            'due_date.date' => 'Geçerli bir son tarih giriniz.',
            'priority.required' => 'Öncelik seviyesi gereklidir.',
            'priority.integer' => 'Öncelik seviyesi sayı olmalıdır.',
            'priority.min' => 'Öncelik seviyesi en az 1 olabilir.',
            'priority.max' => 'Öncelik seviyesi en fazla 3 olabilir.',
            'status.required' => 'Durum gereklidir.',
            'status.in' => 'Geçersiz durum değeri.',
            'category_ids.array' => 'Kategoriler dizi formatında olmalıdır.',
            'category_ids.*.exists' => 'Seçilen kategori bulunamadı.',
        ];
    }
} 