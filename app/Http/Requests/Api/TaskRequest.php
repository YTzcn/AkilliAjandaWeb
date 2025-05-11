<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class TaskRequest extends FormRequest
{
    /**
     * Kullanıcının bu isteği yapmaya yetkisi olup olmadığını belirler.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * İsteğe uygulanan doğrulama kurallarını döndürür.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'priority' => 'nullable|integer|min:1|max:3',
            'status' => 'nullable|string|in:pending,in-progress,completed',
            'is_completed' => 'boolean',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
        ];
    }

    /**
     * İsteğe uygulanan doğrulama mesajlarını döndürür.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Görev başlığı gereklidir.',
            'title.max' => 'Görev başlığı en fazla 255 karakter olabilir.',
            'due_date.required' => 'Son tarih gereklidir.',
            'due_date.date' => 'Son tarih geçerli bir tarih olmalıdır.',
            'priority.integer' => 'Öncelik seviyesi sayı olmalıdır.',
            'priority.min' => 'Öncelik seviyesi en az 1 olabilir.',
            'priority.max' => 'Öncelik seviyesi en fazla 3 olabilir.',
            'status.in' => 'Geçersiz durum değeri.',
            'category_ids.array' => 'Kategoriler dizi formatında olmalıdır.',
            'category_ids.*.exists' => 'Seçilen kategori bulunamadı.',
        ];
    }
} 