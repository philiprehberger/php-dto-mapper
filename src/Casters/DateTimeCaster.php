<?php

declare(strict_types=1);

namespace PhilipRehberger\DtoMapper\Casters;

use DateTimeImmutable;
use PhilipRehberger\DtoMapper\Contracts\Caster;

/**
 * Casts a string value to a DateTimeImmutable instance.
 */
class DateTimeCaster implements Caster
{
    /**
     * Create a new DateTimeCaster.
     */
    public function __construct(
        private readonly string $format = '',
    ) {}

    /**
     * Cast the value to DateTimeImmutable.
     */
    public function cast(mixed $value): DateTimeImmutable
    {
        if ($value instanceof DateTimeImmutable) {
            return $value;
        }

        if (! is_string($value)) {
            throw new \InvalidArgumentException(
                sprintf('DateTimeCaster expects a string, got %s.', get_debug_type($value))
            );
        }

        if ($this->format !== '') {
            $result = DateTimeImmutable::createFromFormat($this->format, $value);

            if ($result === false) {
                throw new \InvalidArgumentException(
                    sprintf('Failed to parse "%s" with format "%s".', $value, $this->format)
                );
            }

            return $result;
        }

        try {
            return new DateTimeImmutable($value);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException(
                sprintf('Failed to parse date string "%s": %s', $value, $e->getMessage()),
                previous: $e,
            );
        }
    }
}
