<?php

namespace App\Http\Controllers;

use App\Application\Offers\DTO\OfferData;
use App\Application\Offers\Services\OfferService;
use App\Domain\Offers\Queries\OfferQuery;
use App\Domain\Offers\ValueObjects\OfferId;
use App\Http\Requests\Offer\StoreOfferRequest;
use App\Http\Requests\Offer\UpdateOfferRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OfferController extends Controller
{
    public function create(): View
    {
        $this->authorize('admin');

        return view('offers.create');
    }

    public function store(StoreOfferRequest $request, OfferService $service): RedirectResponse
    {
        $this->authorize('admin');

        /** @var array{name: string, slug: string, description?: string|null, state: string} $validated */
        $validated = $request->validated();
        $data = OfferData::fromArray($validated, $request->file('image'));
        $service->create($data);

        return redirect()->route('dashboard');
    }

    public function edit(string $offerId, OfferQuery $query): View
    {
        $this->authorize('admin');

        $offer = $query->get($this->toOfferId($offerId));
        if (! $offer) {
            abort(404);
        }

        return view('offers.edit', compact('offer'));
    }

    public function update(UpdateOfferRequest $request, string $offerId, OfferService $service): RedirectResponse
    {
        $this->authorize('admin');

        /** @var array{name: string, slug: string, description?: string|null, state: string} $validated */
        $validated = $request->validated();
        $data = OfferData::fromArray($validated, $request->file('image'));
        $updated = $service->update($this->toOfferId($offerId), $data);
        if (! $updated) {
            abort(404);
        }

        return redirect()->route('dashboard');
    }

    public function destroy(string $offerId, OfferService $service): RedirectResponse
    {
        $this->authorize('admin');

        $service->delete($this->toOfferId($offerId));

        return redirect()->route('dashboard');
    }

    public function show(string $offerId, OfferQuery $query): View
    {
        $this->authorize('admin');

        $offer = $query->getWithProducts($this->toOfferId($offerId));
        if (! $offer) {
            abort(404);
        }

        return view('offers.show', compact('offer'));
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
}
