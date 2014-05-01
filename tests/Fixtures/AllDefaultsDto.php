<?php

declare(strict_types=1);

namespace PhilipRehberger\DtoMapper\Tests\Fixtures;

use PhilipRehberger\DtoMapper\Attributes\Optional;

class AllDefaultsDto
{
    public function __construct(
        #[Optional]
        public readonly string $name = 'default',
        #[Optional]
        public readonly int $count = 0,
        #[Optional]
        public readonly bool $enabled = false,
    ) {}
}
