<?php

declare(strict_types=1);

namespace App\Http\Controllers\Concerns;

use App\Domain\Shared\ValueObjects\PaginatedResult;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

trait PaginatesDomainResults
{
    /**
     * @return LengthAwarePaginator<mixed>
     */
    private function toPaginator(Request $request, PaginatedResult $result): LengthAwarePaginator
    {
        return new LengthAwarePaginator(
            $result->items,
            $result->total,
            $result->perPage,
            $result->page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }
}
