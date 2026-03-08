<?php

namespace App\Http\Controllers\Api;

use App\Domain\Offers\Queries\OfferQuery;
use App\Domain\Shared\ValueObjects\PageRequest;
use App\Domain\Shared\ValueObjects\PaginatedResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PublicOfferIndexRequest;
use App\Http\Resources\OfferResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

class OfferController extends Controller
{
    public function index(PublicOfferIndexRequest $request, OfferQuery $query): AnonymousResourceCollection
    {
        $perPage = max(1, $request->integer('per_page', 15));
        $page = max(1, $request->integer('page', 1));
        $result = $query->listPublished(new PageRequest($page, $perPage));
        $offers = $this->toPaginator($request, $result);

        return OfferResource::collection($offers);
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
