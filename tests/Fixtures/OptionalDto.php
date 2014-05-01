<?php

declare(strict_types=1);

namespace PhilipRehberger\DtoMapper\Tests\Fixtures;

use PhilipRehberger\DtoMapper\Attributes\Optional;

class OptionalDto
{
    public function __construct(
        public readonly string $name,
        #[Optional]
        public readonly string $nickname = 'none',
        #[Optional]
        public readonly ?int $age = null,
    ) {}
}
