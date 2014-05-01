<?php

declare(strict_types=1);

namespace PhilipRehberger\DtoMapper\Contracts;

/**
 * Contract for custom type casters.
 */
interface Caster
{
    /**
     * Cast the given value to the target type.
     */
    public function cast(mixed $value): mixed;
}
