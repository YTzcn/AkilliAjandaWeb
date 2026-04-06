<?php

namespace App\Support;

/**
 * Web ve API'den gelen görev listesi sorgu parametrelerini tek forma getirir.
 *
 * @phpstan-type TaskListFilters array<string, mixed>
 */
class TaskListFilterNormalizer
{
    /**
     * @param  array<string, mixed>  $raw
     * @return array<string, mixed>
     */
    public static function normalize(array $raw): array
    {
        $keys = [
            'status', 'priority', 'is_completed', 'due_from', 'due_to', 'due_date',
            'category_id', 'sort', 'dir', 'q',
        ];
        $filters = [];
        foreach ($keys as $key) {
            if (! array_key_exists($key, $raw)) {
                continue;
            }
            $val = $raw[$key];
            if ($val === null || $val === '') {
                continue;
            }
            $filters[$key] = $val;
        }

        if (isset($filters['due_date']) && ! isset($filters['due_from']) && ! isset($filters['due_to'])) {
            $filters['due_from'] = $filters['due_to'] = $filters['due_date'];
        }
        unset($filters['due_date']);

        if (isset($filters['priority'])) {
            $filters['priority'] = (int) $filters['priority'];
        }

        $filters['sort'] = $filters['sort'] ?? 'due_date';
        $filters['dir'] = $filters['dir'] ?? 'asc';

        $allowedSorts = ['due_date', 'priority', 'created_at', 'title', 'status'];
        if (! in_array($filters['sort'], $allowedSorts, true)) {
            $filters['sort'] = 'due_date';
        }
        $filters['dir'] = strtolower((string) $filters['dir']) === 'desc' ? 'desc' : 'asc';

        return $filters;
    }
}
