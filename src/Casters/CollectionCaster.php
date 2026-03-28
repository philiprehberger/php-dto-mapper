<?php

declare(strict_types=1);

namespace PhilipRehberger\DtoMapper\Casters;

use PhilipRehberger\DtoMapper\Contracts\Caster;
use PhilipRehberger\DtoMapper\DtoMapper;

/**
 * Casts an array of arrays to an array of DTO instances.
 */
class CollectionCaster implements Caster
{
    /**
     * Create a new CollectionCaster.
     *
     * @param  class-string  $targetClass
     */
    public function __construct(
        private readonly string $targetClass,
    ) {}

    /**
     * Cast the value to an array of DTO instances.
     *
     * @return array<object>
     */
    public function cast(mixed $value): array
    {
        if (! is_array($value)) {
            throw new \InvalidArgumentException(
                sprintf('CollectionCaster expects an array, got %s.', get_debug_type($value))
            );
        }

        return array_map(
            fn (array $item): object => DtoMapper::map($item, $this->targetClass),
            $value,
        );
    }
}
