<?php

declare(strict_types=1);

namespace PhilipRehberger\DtoMapper\Tests\Fixtures;

class PartialDto
{
    public function __construct(
        public readonly string $name,
        public readonly int $age,
        public readonly ?string $email = null,
        public readonly string $role = 'user',
    ) {}
}
