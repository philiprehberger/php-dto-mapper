<?php

declare(strict_types=1);

namespace PhilipRehberger\DtoMapper;

/**
 * Handles automatic type coercion for scalar values.
 */
class TypeCoercer
{
    /**
     * Coerce a value to the first matching type in a union.
     *
     * @param  array<array{typeName: string, isBuiltin: bool}>  $unionTypes
     */
    public static function coerceUnion(mixed $value, array $unionTypes): mixed
    {
        if ($value === null) {
            return null;
        }

        // First, check if the value already matches one of the types natively
        $nativeType = get_debug_type($value);

        foreach ($unionTypes as $type) {
            if ($type['isBuiltin'] && $nativeType === $type['typeName']) {
                return $value;
            }
        }

        // Try coercing to each type in declaration order
        foreach ($unionTypes as $type) {
            if (! $type['isBuiltin']) {
                continue;
            }

            try {
                $coerced = self::coerce($value, $type['typeName']);
                $actualType = get_debug_type($coerced);

                if ($actualType === $type['typeName']) {
                    return $coerced;
                }
            } catch (\Throwable) {
                continue;
            }
        }

        return $value;
    }

    /**
     * Coerce a value to the specified type name.
     */
    public static function coerce(mixed $value, string $typeName): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($typeName) {
            'int' => self::toInt($value),
            'float' => self::toFloat($value),
            'bool' => self::toBool($value),
            'string' => self::toString($value),
            'array' => self::toArray($value),
            default => $value,
        };
    }

    /**
     * Coerce a value to integer.
     */
    private static function toInt(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && is_numeric($value)) {
            return (int) $value;
        }

        if (is_float($value)) {
            return (int) $value;
        }

        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        return (int) $value;
    }

    /**
     * Coerce a value to float.
     */
    private static function toFloat(mixed $value): float
    {
        if (is_float($value)) {
            return $value;
        }

        if (is_string($value) && is_numeric($value)) {
            return (float) $value;
        }

        if (is_int($value)) {
            return (float) $value;
        }

        return (float) $value;
    }

    /**
     * Coerce a value to boolean.
     */
    private static function toBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            return match (strtolower($value)) {
                'true', '1', 'yes', 'on' => true,
                'false', '0', 'no', 'off', '' => false,
                default => (bool) $value,
            };
        }

        return (bool) $value;
    }

    /**
     * Coerce a value to string.
     */
    private static function toString(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return (string) $value;
    }

    /**
     * Coerce a value to array.
     *
     * @return array<mixed>
     */
    private static function toArray(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        return (array) $value;
    }
}
