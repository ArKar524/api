<?php

namespace App\Support\QueryFilters;

use Illuminate\Database\Eloquent\Builder;

trait Filterable
{
    public function scopeApplyFilters(Builder $query, array $filters, array $allowed): Builder
    {
        foreach ($filters as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if (!array_key_exists($key, $allowed)) {
                continue;
            }

            $callback = $allowed[$key];
            if (is_callable($callback)) {
                $callback($query, $value);
            }
        }

        return $query;
    }

    public function scopeApplySearch(Builder $query, ?string $term, array $columns): Builder
    {
        if (!$term) {
            return $query;
        }

        $like = '%' . str_replace('%', '\\%', $term) . '%';

        return $query->where(function (Builder $q) use ($columns, $like) {
            foreach ($columns as $index => $column) {
                $index === 0
                    ? $q->where($column, 'like', $like)
                    : $q->orWhere($column, 'like', $like);
            }
        });
    }
}
