<?php

declare(strict_types=1);

namespace PhilipRehberger\DtoMapper\Casters;

use PhilipRehberger\DtoMapper\Contracts\Caster;

/**
 * Casts a string or integer value to a backed enum.
 */
class EnumCaster implements Caster
{
    /**
     * Create a new EnumCaster.
     *
     * @param  class-string<\BackedEnum>  $enumClass
     */
    public function __construct(
        private readonly string $enumClass,
    ) {}

    /**
     * Cast the value to the specified backed enum.
     */
    public function cast(mixed $value): \BackedEnum
    {
        if ($value instanceof $this->enumClass) {
            return $value;
        }

        if (! is_string($value) && ! is_int($value)) {
            throw new \InvalidArgumentException(
                sprintf('EnumCaster expects a string or int, got %s.', get_debug_type($value))
            );
        }

        $enum = $this->enumClass::tryFrom($value);

        if ($enum === null) {
            throw new \InvalidArgumentException(
                sprintf('Value "%s" is not valid for enum %s.', (string) $value, $this->enumClass)
            );
        }

        return $enum;
    }
}
