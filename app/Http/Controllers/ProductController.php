<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Products\DTO\ProductData;
use App\Application\Products\Services\ProductService;
use App\Domain\Offers\Queries\OfferQuery;
use App\Domain\Products\Queries\ProductQuery;
use App\Domain\Shared\ValueObjects\PageRequest;
use App\Http\Controllers\Concerns\PaginatesDomainResults;
use App\Http\Controllers\Concerns\ResolvesDomainIds;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    use PaginatesDomainResults;
    use ResolvesDomainIds;

    public function index(Request $request, string $offerId, OfferQuery $offerQuery, ProductQuery $productQuery): View
    {
        $this->authorize('admin');

        $offer = $offerQuery->get($this->offerIdFromRoute($offerId));
        if (! $offer) {
            abort(404);
        }

        $pageNumber = max(1, (int) $request->query('page', 1));
        $page = new PageRequest($pageNumber, 20);
        $result = $productQuery->listForOffer($this->offerIdFromRoute($offerId), $page);
        $products = $this->toPaginator($request, $result);

        return view('products.index', compact('offer', 'products'));
    }

    public function create(string $offerId, OfferQuery $offerQuery): View
    {
        $this->authorize('admin');

        $offer = $offerQuery->get($this->offerIdFromRoute($offerId));
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
        $service->create($this->offerIdFromRoute($offerId), $data);

        return redirect()
            ->route('offers.products.index', $offerId)
            ->with('status', 'Product created successfully.');
    }

    public function edit(string $offerId, string $productId, OfferQuery $offerQuery, ProductQuery $productQuery): View
    {
        $this->authorize('admin');

        $offer = $offerQuery->get($this->offerIdFromRoute($offerId));
        $product = $productQuery->getForOffer(
            $this->offerIdFromRoute($offerId),
            $this->productIdFromRoute($productId)
        );
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
        $updated = $service->update(
            $this->offerIdFromRoute($offerId),
            $this->productIdFromRoute($productId),
            $data
        );
        if (! $updated) {
            abort(404);
        }

        return redirect()
            ->route('offers.products.index', $offerId)
            ->with('status', 'Product updated successfully.');
    }

    public function destroy(string $offerId, string $productId, ProductService $service): RedirectResponse
    {
        $this->authorize('admin');

        $deleted = $service->delete($this->offerIdFromRoute($offerId), $this->productIdFromRoute($productId));
        if (! $deleted) {
            abort(404);
        }

        return redirect()
            ->route('offers.products.index', $offerId)
            ->with('status', 'Product deleted successfully.');
    }
}
