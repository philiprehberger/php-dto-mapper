<?php

declare(strict_types=1);

namespace PhilipRehberger\DtoMapper\Tests\Fixtures;

use DateTimeImmutable;
use PhilipRehberger\DtoMapper\Attributes\CastWith;
use PhilipRehberger\DtoMapper\Casters\DateTimeCaster;

class DateDto
{
    public function __construct(
        public readonly string $label,
        #[CastWith(DateTimeCaster::class)]
        public readonly DateTimeImmutable $createdAt,
    ) {}
}
