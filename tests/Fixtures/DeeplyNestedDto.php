<?php

declare(strict_types=1);

namespace PhilipRehberger\DtoMapper\Tests\Fixtures;

class DeeplyNestedDto
{
    public function __construct(
        public readonly string $name,
        public readonly DeepAddressDto $address,
    ) {}
}
