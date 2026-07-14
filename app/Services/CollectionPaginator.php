<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CollectionPaginator
{
    public function paginate(mixed $items, int $perPage = 15, array|Request $queryInput = []): LengthAwarePaginator
    {
        if (is_array($items)) {
            $items = new Collection($items);
        }

        $queryParameters = $queryInput instanceof Request
            ? $queryInput->query()
            : $queryInput;

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $total = $items->count();
        $currentItems = $items->slice(($currentPage - 1) * $perPage, $perPage)->all();

        return new LengthAwarePaginator($currentItems, $total, $perPage, $currentPage, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
            'query' => $queryParameters,
        ]);
    }
}
