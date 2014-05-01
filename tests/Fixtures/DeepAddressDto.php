<?php

declare(strict_types=1);

namespace PhilipRehberger\DtoMapper\Tests\Fixtures;

class DeepAddressDto
{
    public function __construct(
        public readonly string $street,
        public readonly string $city,
        public readonly CountryDto $country,
    ) {}
}
