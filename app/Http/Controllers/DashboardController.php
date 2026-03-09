<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Offers\Queries\OfferFilters;
use App\Application\Offers\Queries\OfferQuery;
use App\Domain\Shared\ValueObjects\PageRequest;
use App\Http\Controllers\Concerns\PaginatesDomainResults;
use App\Http\Requests\Offer\IndexOfferRequest;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use PaginatesDomainResults;

    public function show(IndexOfferRequest $request, OfferQuery $query): View
    {
        $this->authorize('admin');

        $filters = OfferFilters::fromArray($request->validated());
        $pageNumber = max(1, $request->integer('page', 1));
        $page = new PageRequest($pageNumber, 15);
        $result = $query->list($filters, $page);
        $offers = $this->toPaginator($request, $result);

        return view('dashboard', ['offers' => $offers]);
    }
}
