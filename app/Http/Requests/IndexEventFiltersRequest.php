<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexEventFiltersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()?->id;
        $categoryRule = ['nullable', 'integer'];
        if ($userId !== null) {
            $categoryRule[] = Rule::exists('categories', 'id')->where('user_id', $userId);
        }

        return [
            'category_id' => $categoryRule,
            'q' => ['nullable', 'string', 'max:200'],
        ];
    }
}
