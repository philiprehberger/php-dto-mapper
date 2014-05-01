<?php

declare(strict_types=1);

namespace PhilipRehberger\DtoMapper\Tests\Fixtures;

use PhilipRehberger\DtoMapper\Attributes\MapFrom;

class MappedDto
{
    public function __construct(
        #[MapFrom('full_name')]
        public readonly string $name,
        #[MapFrom('user_email')]
        public readonly string $email,
    ) {}
}
