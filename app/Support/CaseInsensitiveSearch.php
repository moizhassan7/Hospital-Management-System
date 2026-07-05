<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class CaseInsensitiveSearch
{
    public static function pattern(string $term): string
    {
        return '%' . mb_strtolower($term) . '%';
    }

    /**
     * Case-insensitive LIKE for PostgreSQL/MySQL (LOWER on both sides).
     *
     * @param  EloquentBuilder|QueryBuilder  $query
     */
    public static function whereLike(EloquentBuilder|QueryBuilder $query, string $column, string $term): EloquentBuilder|QueryBuilder
    {
        return $query->whereRaw('LOWER(' . $column . ') LIKE ?', [self::pattern($term)]);
    }
}
