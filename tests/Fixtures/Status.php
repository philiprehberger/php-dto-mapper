<?php

declare(strict_types=1);

namespace PhilipRehberger\DtoMapper\Tests\Fixtures;

enum Status: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Pending = 'pending';
}
