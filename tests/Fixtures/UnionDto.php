<?php

declare(strict_types=1);

namespace PhilipRehberger\DtoMapper\Tests\Fixtures;

class UnionDto
{
    public function __construct(
        public readonly string $name,
        public readonly int|string $identifier,
    ) {}
}
