<?php

declare(strict_types=1);

namespace App\Shared\Query;

enum SortDirection: string
{
    case ASC = 'asc';
    case DESC = 'desc';
}
