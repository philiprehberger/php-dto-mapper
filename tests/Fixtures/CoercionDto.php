<?php

declare(strict_types=1);

namespace PhilipRehberger\DtoMapper\Tests\Fixtures;

class CoercionDto
{
    public function __construct(
        public readonly int $count,
        public readonly float $price,
        public readonly bool $active,
    ) {}
}
