<?php

declare(strict_types=1);

namespace PhilipRehberger\DtoMapper\Tests\Fixtures;

use PhilipRehberger\DtoMapper\Attributes\MapFrom;

class DotNotationDto
{
    public function __construct(
        public readonly string $name,
        #[MapFrom('user.profile.email')]
        public readonly string $email,
        #[MapFrom('user.age')]
        public readonly int $age,
    ) {}
}
