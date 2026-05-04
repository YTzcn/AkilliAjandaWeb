<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * Takvim API’si için görünür tarih aralığını güvenli ve sınırlı bir pencereye indirger.
 */
final class CalendarQueryWindow
{
    public const MAX_SPAN_DAYS = 120;

    /**
     * @return array{start: Carbon, end: Carbon}|null İkisi de dolu değilse null.
     */
    public static function fromRequest(?string $start, ?string $end): ?array
    {
        if ($start === null || $end === null || $start === '' || $end === '') {
            return null;
        }

        $s = Carbon::parse($start)->startOfDay();
        $e = Carbon::parse($end)->endOfDay();

        if ($e->lt($s)) {
            [$s, $e] = [$e->copy()->startOfDay(), $s->copy()->endOfDay()];
        }

        if ($s->diffInDays($e) > self::MAX_SPAN_DAYS) {
            $e = $s->copy()->addDays(self::MAX_SPAN_DAYS)->endOfDay();
        }

        return ['start' => $s, 'end' => $e];
    }
}
