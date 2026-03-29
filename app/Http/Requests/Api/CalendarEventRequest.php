<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="CalendarEventRequest",
 *     title="Takvim Etkinliği İsteği",
 *     description="Takvim etkinliği oluşturma ve güncelleme için kullanılan form request",
 *     required={"title", "start_date", "end_date"},
 *     @OA\Property(
 *         property="title",
 *         type="string",
 *         maxLength=255,
 *         description="Etkinlik başlığı",
 *         example="Toplantı"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         nullable=true,
 *         description="Etkinlik açıklaması",
 *         example="Haftalık ekip toplantısı"
 *     ),
 *     @OA\Property(
 *         property="start_date",
 *         type="string",
 *         format="date-time",
 *         description="Başlangıç tarihi ve saati",
 *         example="2024-03-20T10:00:00"
 *     ),
 *     @OA\Property(
 *         property="end_date",
 *         type="string",
 *         format="date-time",
 *         description="Bitiş tarihi ve saati",
 *         example="2024-03-20T11:00:00"
 *     ),
 *     @OA\Property(
 *         property="all_day",
 *         type="boolean",
 *         description="Tüm gün etkinliği mi?",
 *         example=false
 *     ),
 *     @OA\Property(
 *         property="location",
 *         type="string",
 *         nullable=true,
 *         maxLength=255,
 *         description="Etkinlik konumu",
 *         example="Toplantı Odası 1"
 *     )
 * )
 */
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