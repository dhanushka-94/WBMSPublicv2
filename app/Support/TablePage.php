<?php

namespace App\Support;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class TablePage
{
    public const SIZE = 25;

    public static function collection(Collection $items, string $pageName = 'page'): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage($pageName);

        return new LengthAwarePaginator(
            $items->forPage($page, self::SIZE)->values(),
            $items->count(),
            self::SIZE,
            $page,
            [
                'pageName' => $pageName,
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );
    }
}
