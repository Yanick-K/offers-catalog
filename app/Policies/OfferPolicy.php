<?php

namespace App\Policies;

use App\Models\Offer;
use App\Models\User;

class OfferPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_admin;
    }

    public function view(User $user, Offer $offer): bool
    {
        return $user->is_admin;
    }

    public function create(User $user): bool
    {
        return $user->is_admin;
    }

    public function update(User $user, Offer $offer): bool
    {
        return $user->is_admin;
    }

    public function delete(User $user, Offer $offer): bool
    {
        return $user->is_admin;
    }
}
