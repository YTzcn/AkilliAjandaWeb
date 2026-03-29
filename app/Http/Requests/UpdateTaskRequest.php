<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
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
            'due_date' => 'required|date',
            'priority' => 'nullable|integer|min:1|max:3',
            'status' => 'nullable|string|in:pending,in-progress,completed,cancelled',
            'is_completed' => 'boolean',
            'category_ids' => 'nullable|array',
            'category_ids.*' => [
                'integer',
                Rule::exists('categories', 'id')->where('user_id', auth()->id()),
            ],
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
            'title.required' => 'Görev başlığı gereklidir.',
            'title.max' => 'Görev başlığı en fazla 255 karakter olabilir.',
            'due_date.required' => 'Son tarih gereklidir.',
            'due_date.date' => 'Son tarih geçerli bir tarih olmalıdır.',
        ];
    }
} 