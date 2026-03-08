<?php

namespace App\Http\Controllers;

use App\Domain\Offers\OfferFilters;
use App\Domain\Offers\Queries\OfferQuery;
use App\Domain\Shared\ValueObjects\PageRequest;
use App\Domain\Shared\ValueObjects\PaginatedResult;
use App\Http\Requests\Offer\OfferIndexRequest;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function show(OfferIndexRequest $request, OfferQuery $query): View
    {
        $this->authorize('admin');

        $filters = OfferFilters::fromArray($request->validated());
        $pageNumber = max(1, $request->integer('page', 1));
        $page = new PageRequest($pageNumber, 15);
        $result = $query->list($filters, $page);
        $offers = $this->toPaginator($request, $result);

        return view('dashboard', ['offers' => $offers]);
    }

    /**
     * @return LengthAwarePaginator<\App\Domain\Offers\Entities\Offer>
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
