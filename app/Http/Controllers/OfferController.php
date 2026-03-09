<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Offers\DTO\OfferData;
use App\Application\Offers\Queries\OfferQuery;
use App\Application\Offers\Services\OfferCommandService;
use App\Http\Controllers\Concerns\BuildsImageUploads;
use App\Http\Controllers\Concerns\ResolvesDomainIds;
use App\Http\Requests\Offer\StoreOfferRequest;
use App\Http\Requests\Offer\UpdateOfferRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OfferController extends Controller
{
    use BuildsImageUploads;
    use ResolvesDomainIds;

    public function create(): View
    {
        $this->authorize('admin');

        return view('offers.create');
    }

    public function store(StoreOfferRequest $request, OfferCommandService $service): RedirectResponse
    {
        $this->authorize('admin');

        /** @var array{name: string, slug: string, description?: string|null, state: string} $validated */
        $validated = $request->validated();
        $data = OfferData::fromArray($validated, $this->toImageUpload($request->file('image')));
        $service->create($data);

        return redirect()->route('dashboard');
    }

    public function edit(string $offerId, OfferQuery $query): View
    {
        $this->authorize('admin');

        $offer = $query->find($this->offerIdFromRoute($offerId));
        if (! $offer) {
            abort(404);
        }

        return view('offers.edit', compact('offer'));
    }

    public function update(UpdateOfferRequest $request, string $offerId, OfferCommandService $service): RedirectResponse
    {
        $this->authorize('admin');

        /** @var array{name: string, slug: string, description?: string|null, state: string} $validated */
        $validated = $request->validated();
        $data = OfferData::fromArray($validated, $this->toImageUpload($request->file('image')));
        $updated = $service->update($this->offerIdFromRoute($offerId), $data);
        if (! $updated) {
            abort(404);
        }

        return redirect()->route('dashboard');
    }

    public function destroy(string $offerId, OfferCommandService $service): RedirectResponse
    {
        $this->authorize('admin');

        $service->delete($this->offerIdFromRoute($offerId));

        return redirect()->route('dashboard');
    }

    public function show(string $offerId, OfferQuery $query): View
    {
        $this->authorize('admin');

        $offer = $query->find($this->offerIdFromRoute($offerId));
        if (! $offer) {
            abort(404);
        }

        return view('offers.show', compact('offer'));
    }
}
