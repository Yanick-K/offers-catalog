<?php

namespace App\Http\Controllers;

use App\Application\Products\DTO\ProductData;
use App\Application\Products\Services\ProductService;
use App\Domain\Offers\Queries\OfferQuery;
use App\Domain\Offers\ValueObjects\OfferId;
use App\Domain\Products\Queries\ProductQuery;
use App\Domain\Products\ValueObjects\ProductId;
use App\Domain\Shared\ValueObjects\PageRequest;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request, string $offerId, OfferQuery $offerQuery, ProductQuery $productQuery): View
    {
        $this->authorize('admin');

        $offer = $offerQuery->get($this->toOfferId($offerId));
        if (! $offer) {
            abort(404);
        }

        $pageNumber = max(1, (int) $request->query('page', 1));
        $page = new PageRequest($pageNumber, 20);
        $result = $productQuery->listForOffer($this->toOfferId($offerId), $page);
        $products = $this->toPaginator($request, $result);

        return view('products.index', compact('offer', 'products'));
    }

    public function create(string $offerId, OfferQuery $offerQuery): View
    {
        $this->authorize('admin');

        $offer = $offerQuery->get($this->toOfferId($offerId));
        if (! $offer) {
            abort(404);
        }

        return view('products.create', compact('offer'));
    }

    public function store(StoreProductRequest $request, string $offerId, ProductService $service): RedirectResponse
    {
        $this->authorize('admin');

        /** @var array{name: string, sku: string, price: float|int|string, state: string} $validated */
        $validated = $request->validated();
        $data = ProductData::fromArray($validated, $request->file('image'));
        $service->create($this->toOfferId($offerId), $data);

        return redirect()
            ->route('offers.products.index', $offerId)
            ->with('status', 'Produit créé avec succès.');
    }

    public function edit(string $offerId, string $productId, OfferQuery $offerQuery, ProductQuery $productQuery): View
    {
        $this->authorize('admin');

        $offer = $offerQuery->get($this->toOfferId($offerId));
        $product = $productQuery->getForOffer($this->toOfferId($offerId), $this->toProductId($productId));
        if (! $offer || ! $product) {
            abort(404);
        }

        return view('products.edit', compact('offer', 'product'));
    }

    public function update(UpdateProductRequest $request, string $offerId, string $productId, ProductService $service): RedirectResponse
    {
        $this->authorize('admin');

        /** @var array{name: string, sku: string, price: float|int|string, state: string} $validated */
        $validated = $request->validated();
        $data = ProductData::fromArray($validated, $request->file('image'));
        $updated = $service->update($this->toOfferId($offerId), $this->toProductId($productId), $data);
        if (! $updated) {
            abort(404);
        }

        return redirect()
            ->route('offers.products.index', $offerId)
            ->with('status', 'Produit mis à jour avec succès.');
    }

    public function destroy(string $offerId, string $productId, ProductService $service): RedirectResponse
    {
        $this->authorize('admin');

        $deleted = $service->delete($this->toOfferId($offerId), $this->toProductId($productId));
        if (! $deleted) {
            abort(404);
        }

        return redirect()
            ->route('offers.products.index', $offerId)
            ->with('status', 'Produit supprimé avec succès.');
    }

    private function toOfferId(string $offerId): OfferId
    {
        if (! ctype_digit($offerId)) {
            abort(404);
        }

        $id = (int) $offerId;
        if ($id < 1) {
            abort(404);
        }

        return new OfferId($id);
    }

    private function toProductId(string $productId): ProductId
    {
        if (! ctype_digit($productId)) {
            abort(404);
        }

        $id = (int) $productId;
        if ($id < 1) {
            abort(404);
        }

        return new ProductId($id);
    }

    /**
     * @return LengthAwarePaginator<\App\Domain\Products\Entities\Product>
     */
    private function toPaginator(Request $request, \App\Domain\Shared\ValueObjects\PaginatedResult $result): LengthAwarePaginator
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
