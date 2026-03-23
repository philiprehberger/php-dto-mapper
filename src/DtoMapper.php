<?php

declare(strict_types=1);

namespace PhilipRehberger\DtoMapper;

use JsonException;
use PhilipRehberger\DtoMapper\Exceptions\MappingException;
use ReflectionClass;
use Throwable;

/**
 * Map arrays and JSON to strongly-typed DTOs with attribute-driven configuration.
 */
class DtoMapper
{
    /**
     * Map an associative array to a DTO instance.
     *
     * @template T of object
     *
     * @param  array<string, mixed>  $data
     * @param  class-string<T>  $class
     * @return T
     *
     * @throws MappingException
     */
    public static function map(array $data, string $class): object
    {
        $errors = [];
        $resolved = PropertyResolver::resolve($class);
        $reflection = new ReflectionClass($class);
        $values = [];

        foreach ($resolved as $name => $meta) {
            $sourceKey = $meta['sourceKey'];
            $hasKey = array_key_exists($sourceKey, $data);

            if (! $hasKey) {
                if ($meta['optional'] || $meta['hasDefault']) {
                    $values[$name] = $meta['default'];

                    continue;
                }

                if ($meta['nullable']) {
                    $values[$name] = null;

                    continue;
                }

                $errors[] = sprintf('Missing required field "%s".', $sourceKey);

                continue;
            }

            $value = $data[$sourceKey];

            try {
                $values[$name] = self::resolveValue($value, $meta);
            } catch (Throwable $e) {
                $errors[] = sprintf('Field "%s": %s', $sourceKey, $e->getMessage());
            }
        }

        if (count($errors) > 0) {
            throw new MappingException($errors);
        }

        return self::instantiate($reflection, $resolved, $values);
    }

    /**
     * Map a JSON string to a DTO instance.
     *
     * @template T of object
     *
     * @param  class-string<T>  $class
     * @return T
     *
     * @throws MappingException
     */
    public static function mapJson(string $json, string $class): object
    {
        try {
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new MappingException(
                [sprintf('Invalid JSON: %s', $e->getMessage())],
            );
        }

        if (! is_array($data)) {
            throw new MappingException(['JSON must decode to an array.']);
        }

        return self::map($data, $class);
    }

    /**
     * Map an array of arrays to a collection of DTO instances.
     *
     * @template T of object
     *
     * @param  array<array<string, mixed>>  $items
     * @param  class-string<T>  $class
     * @return array<T>
     *
     * @throws MappingException
     */
    public static function mapCollection(array $items, string $class): array
    {
        return array_map(
            fn (array $item): object => self::map($item, $class),
            $items,
        );
    }

    /**
     * Map an associative array to a DTO without requiring all fields.
     *
     * Missing fields get their default value or null for nullable properties.
     * Non-nullable properties without defaults that are missing are skipped (not set).
     *
     * @template T of object
     *
     * @param  array<string, mixed>  $data
     * @param  class-string<T>  $class
     * @return T
     *
     * @throws MappingException
     */
    public static function mapPartial(array $data, string $class): object
    {
        $errors = [];
        $resolved = PropertyResolver::resolve($class);
        $reflection = new ReflectionClass($class);
        $values = [];
        $skipped = [];

        foreach ($resolved as $name => $meta) {
            $sourceKey = $meta['sourceKey'];
            $hasKey = array_key_exists($sourceKey, $data);

            if (! $hasKey) {
                if ($meta['hasDefault']) {
                    $values[$name] = $meta['default'];

                    continue;
                }

                if ($meta['nullable']) {
                    $values[$name] = null;

                    continue;
                }

                // Non-nullable without default — skip (do not set)
                $skipped[$name] = true;

                continue;
            }

            $value = $data[$sourceKey];

            try {
                $values[$name] = self::resolveValue($value, $meta);
            } catch (Throwable $e) {
                $errors[] = sprintf('Field "%s": %s', $sourceKey, $e->getMessage());
            }
        }

        if (count($errors) > 0) {
            throw new MappingException($errors);
        }

        return self::instantiatePartial($reflection, $resolved, $values, $skipped);
    }

    /**
     * Attempt to map an array to a DTO, returning null on failure.
     *
     * @template T of object
     *
     * @param  array<string, mixed>  $data
     * @param  class-string<T>  $class
     * @return T|null
     */
    public static function tryMap(array $data, string $class): ?object
    {
        try {
            return self::map($data, $class);
        } catch (MappingException) {
            return null;
        }
    }

    /**
     * Resolve a single property value using caster, nested DTO, or type coercion.
     *
     * @param  array<string, mixed>  $meta
     */
    private static function resolveValue(mixed $value, array $meta): mixed
    {
        if ($value === null && $meta['nullable']) {
            return null;
        }

        // Custom caster takes priority
        if ($meta['caster'] !== null) {
            return $meta['caster']->cast($value);
        }

        $typeName = $meta['typeName'];

        if ($typeName === null) {
            return $value;
        }

        // Handle nested DTOs (non-builtin class types)
        if (! $meta['isBuiltin'] && is_array($value) && class_exists($typeName)) {
            return self::map($value, $typeName);
        }

        // Union type coercion
        if (is_array($meta['unionTypes']) && count($meta['unionTypes']) > 0) {
            return TypeCoercer::coerceUnion($value, $meta['unionTypes']);
        }

        // Type coercion for scalar types
        if ($meta['isBuiltin']) {
            return TypeCoercer::coerce($value, $typeName);
        }

        return $value;
    }

    /**
     * Instantiate the DTO using constructor promotion or direct property assignment.
     *
     * @template T of object
     *
     * @param  ReflectionClass<T>  $reflection
     * @param  array<string, array<string, mixed>>  $resolved
     * @param  array<string, mixed>  $values
     * @return T
     */
    private static function instantiate(ReflectionClass $reflection, array $resolved, array $values): object
    {
        $constructor = $reflection->getConstructor();

        if ($constructor !== null) {
            $args = [];

            foreach ($constructor->getParameters() as $param) {
                $name = $param->getName();

                if (array_key_exists($name, $values)) {
                    $args[] = $values[$name];
                } elseif ($param->isDefaultValueAvailable()) {
                    $args[] = $param->getDefaultValue();
                } else {
                    $args[] = null;
                }
            }

            $instance = $reflection->newInstanceArgs($args);
        } else {
            $instance = $reflection->newInstanceWithoutConstructor();
        }

        // Set any non-promoted properties
        foreach ($resolved as $name => $meta) {
            $property = $meta['property'];

            if ($property->isPromoted()) {
                continue;
            }

            if (array_key_exists($name, $values)) {
                $property->setAccessible(true);
                $property->setValue($instance, $values[$name]);
            }
        }

        return $instance;
    }

    /**
     * Instantiate the DTO for partial mapping, skipping unset properties.
     *
     * @template T of object
     *
     * @param  ReflectionClass<T>  $reflection
     * @param  array<string, array<string, mixed>>  $resolved
     * @param  array<string, mixed>  $values
     * @param  array<string, bool>  $skipped
     * @return T
     */
    private static function instantiatePartial(ReflectionClass $reflection, array $resolved, array $values, array $skipped): object
    {
        $constructor = $reflection->getConstructor();

        if ($constructor !== null) {
            $args = [];

            foreach ($constructor->getParameters() as $param) {
                $name = $param->getName();

                if (array_key_exists($name, $values)) {
                    $args[] = $values[$name];
                } elseif ($param->isDefaultValueAvailable()) {
                    $args[] = $param->getDefaultValue();
                } elseif (isset($skipped[$name])) {
                    // Skip creating via constructor — fall back to property assignment
                    return self::instantiatePartialWithoutConstructor($reflection, $resolved, $values, $skipped);
                } else {
                    $args[] = null;
                }
            }

            $instance = $reflection->newInstanceArgs($args);
        } else {
            $instance = $reflection->newInstanceWithoutConstructor();
        }

        // Set any non-promoted properties
        foreach ($resolved as $name => $meta) {
            $property = $meta['property'];

            if ($property->isPromoted()) {
                continue;
            }

            if (array_key_exists($name, $values) && ! isset($skipped[$name])) {
                $property->setAccessible(true);
                $property->setValue($instance, $values[$name]);
            }
        }

        return $instance;
    }

    /**
     * Instantiate the DTO without constructor for partial mapping.
     *
     * @template T of object
     *
     * @param  ReflectionClass<T>  $reflection
     * @param  array<string, array<string, mixed>>  $resolved
     * @param  array<string, mixed>  $values
     * @param  array<string, bool>  $skipped
     * @return T
     */
    private static function instantiatePartialWithoutConstructor(ReflectionClass $reflection, array $resolved, array $values, array $skipped): object
    {
        $instance = $reflection->newInstanceWithoutConstructor();

        foreach ($resolved as $name => $meta) {
            if (isset($skipped[$name])) {
                continue;
            }

            if (array_key_exists($name, $values)) {
                $property = $meta['property'];
                $property->setAccessible(true);
                $property->setValue($instance, $values[$name]);
            }
        }

        return $instance;
    }
}
