<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

/**
 * Başlık + açıklama üzerinde PostgreSQL ILIKE / SQLite LIKE ile metin araması.
 */
final class RelationalTextSearch
{
    public static function escapeLike(string $term): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $term);
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @param  list<string>  $columns
     */
    public static function apply(Builder $query, string $term, array $columns = ['title', 'description']): void
    {
        $term = trim($term);
        if ($term === '') {
            return;
        }

        $pattern = '%'.self::escapeLike($term).'%';
        $driver = $query->getModel()->getConnection()->getDriverName();

        $query->where(function (Builder $inner) use ($pattern, $columns, $driver) {
            foreach ($columns as $index => $column) {
                $method = $index === 0 ? 'where' : 'orWhere';
                if ($driver === 'pgsql') {
                    $inner->{$method}($column, 'ILIKE', $pattern);
                } else {
                    $inner->{$method}($column, 'like', $pattern);
                }
            }
        });
    }
}
