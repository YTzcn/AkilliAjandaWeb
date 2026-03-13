<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexTaskFiltersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return self::rulesForQuery($this->user()?->id);
    }

    /**
     * @return array<string, mixed>
     */
    public static function rulesForQuery(?int $userId): array
    {
        $categoryRule = ['nullable', 'integer'];
        if ($userId !== null) {
            $categoryRule[] = Rule::exists('categories', 'id')->where('user_id', $userId);
        }

        return [
            'status' => ['nullable', 'string', Rule::in(['pending', 'in-progress', 'completed'])],
            'priority' => ['nullable', 'string', Rule::in(['1', '2', '3'])],
            'is_completed' => ['nullable', 'string', Rule::in(['0', '1'])],
            'due_from' => ['nullable', 'date'],
            'due_to' => ['nullable', 'date', 'after_or_equal:due_from'],
            'due_date' => ['nullable', 'date'],
            'category_id' => $categoryRule,
            'sort' => ['nullable', 'string', Rule::in(['due_date', 'priority', 'created_at', 'title', 'status'])],
            'dir' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
        ];
    }
}
