<?php

declare(strict_types=1);

namespace PhilipRehberger\DtoMapper\Tests\Fixtures;

use PhilipRehberger\DtoMapper\Attributes\CastWith;
use PhilipRehberger\DtoMapper\Casters\EnumCaster;

class EnumDto
{
    public function __construct(
        public readonly string $name,
        #[CastWith(EnumCaster::class, args: [Status::class])]
        public readonly Status $status,
    ) {}
}
