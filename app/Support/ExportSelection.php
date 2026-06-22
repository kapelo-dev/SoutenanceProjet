<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ExportSelection
{
    /**
     * @return list<int>
     */
    public static function ids(Request $request): array
    {
        $ids = $request->input('ids', []);
        if (! is_array($ids)) {
            $ids = [$ids];
        }

        return array_values(array_unique(array_filter(array_map(
            static fn ($id) => is_numeric($id) ? (int) $id : null,
            $ids,
        ))));
    }

    public static function apply(Builder $query, Request $request, string $column = 'id'): void
    {
        $ids = self::ids($request);
        if ($ids !== []) {
            $query->whereIn($column, $ids);
        }
    }
}
