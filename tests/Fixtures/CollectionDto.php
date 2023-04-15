<?php

declare(strict_types=1);

namespace PhilipRehberger\DtoMapper\Tests\Fixtures;

use PhilipRehberger\DtoMapper\Attributes\CastWith;
use PhilipRehberger\DtoMapper\Casters\CollectionCaster;

class CollectionDto
{
    public function __construct(
        public readonly string $name,
        #[CastWith(CollectionCaster::class, args: [SimpleDto::class])]
        public readonly array $items,
    ) {}
}
