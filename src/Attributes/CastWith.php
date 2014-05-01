<?php

declare(strict_types=1);

namespace PhilipRehberger\DtoMapper\Attributes;

use Attribute;

/**
 * Specifies a custom caster class for a property.
 *
 * @template T of \PhilipRehberger\DtoMapper\Contracts\Caster
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class CastWith
{
    /**
     * Create a new CastWith attribute.
     *
     * @param  class-string<T>  $casterClass
     * @param  array<mixed>  $args
     */
    public function __construct(
        public readonly string $casterClass,
        public readonly array $args = [],
    ) {}
}
