<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Offers\Queries\OfferQuery;
use App\Domain\Shared\ValueObjects\PageRequest;
use App\Http\Controllers\Concerns\PaginatesDomainResults;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PublicOfferIndexRequest;
use App\Http\Resources\OfferResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OfferController extends Controller
{
    use PaginatesDomainResults;

    public function index(PublicOfferIndexRequest $request, OfferQuery $query): AnonymousResourceCollection
    {
        $perPage = max(1, $request->integer('per_page', 15));
        $page = max(1, $request->integer('page', 1));
        $result = $query->listPublished(new PageRequest($page, $perPage));
        $offers = $this->toPaginator($request, $result);

        return OfferResource::collection($offers);
    }
}
