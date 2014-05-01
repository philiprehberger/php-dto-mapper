<?php

declare(strict_types=1);

namespace PhilipRehberger\DtoMapper\Attributes;

use Attribute;

/**
 * Maps a property from a different source key.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class MapFrom
{
    /**
     * Create a new MapFrom attribute.
     */
    public function __construct(
        public readonly string $key,
    ) {}
}
