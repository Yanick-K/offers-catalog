<?php

declare(strict_types=1);

namespace App\Application\Offers\Queries;

enum OfferSort: string
{
    case Name = 'name';
    case Slug = 'slug';
    case State = 'state';
    case CreatedAt = 'created_at';
}
