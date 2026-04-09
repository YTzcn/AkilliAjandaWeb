<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class PeriodReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'date_from' => $this->input('date_from') ?: now()->startOfMonth()->toDateString(),
            'date_to' => $this->input('date_to') ?: now()->endOfMonth()->toDateString(),
        ]);
    }

    public function rules(): array
    {
        return [
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            if ($v->errors()->isNotEmpty()) {
                return;
            }
            $from = Carbon::parse($this->input('date_from'))->startOfDay();
            $to = Carbon::parse($this->input('date_to'))->endOfDay();
            if ($from->diffInDays($to) > 366) {
                $v->errors()->add('date_to', 'En fazla 366 günlük aralık seçebilirsiniz.');
            }
        });
    }

    /**
     * @return array{0: \Carbon\Carbon, 1: \Carbon\Carbon}
     */
    public function rangeBoundaries(): array
    {
        $from = Carbon::parse($this->validated('date_from'))->startOfDay();
        $to = Carbon::parse($this->validated('date_to'))->endOfDay();

        return [$from, $to];
    }
}
