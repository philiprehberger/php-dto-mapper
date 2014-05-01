<?php

declare(strict_types=1);

namespace PhilipRehberger\DtoMapper;

use PhilipRehberger\DtoMapper\Attributes\CastWith;
use PhilipRehberger\DtoMapper\Attributes\MapFrom;
use PhilipRehberger\DtoMapper\Attributes\Optional;
use PhilipRehberger\DtoMapper\Contracts\Caster;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;

/**
 * Resolves class properties, their attributes, and type information via Reflection.
 */
class PropertyResolver
{
    /**
     * Resolve all property metadata for the given class.
     *
     * @param  class-string  $class
     * @return array<string, array{
     *     property: ReflectionProperty,
     *     sourceKey: string,
     *     optional: bool,
     *     caster: Caster|null,
     *     typeName: string|null,
     *     nullable: bool,
     *     hasDefault: bool,
     *     default: mixed,
     *     isBuiltin: bool,
     * }>
     */
    public static function resolve(string $class): array
    {
        $reflection = new ReflectionClass($class);
        $properties = $reflection->getProperties();
        $resolved = [];

        foreach ($properties as $property) {
            $name = $property->getName();
            $sourceKey = $name;
            $optional = false;
            $caster = null;
            $typeName = null;
            $nullable = false;
            $isBuiltin = true;
            $hasDefault = $property->hasDefaultValue();
            $default = $hasDefault ? $property->getDefaultValue() : null;

            // Check for promoted parameters with defaults
            if (! $hasDefault && $property->isPromoted()) {
                $constructor = $reflection->getConstructor();

                if ($constructor !== null) {
                    foreach ($constructor->getParameters() as $param) {
                        if ($param->getName() === $name && $param->isDefaultValueAvailable()) {
                            $hasDefault = true;
                            $default = $param->getDefaultValue();

                            break;
                        }
                    }
                }
            }

            // Resolve MapFrom attribute
            $mapFromAttrs = $property->getAttributes(MapFrom::class);

            if (count($mapFromAttrs) > 0) {
                $sourceKey = $mapFromAttrs[0]->newInstance()->key;
            }

            // Resolve Optional attribute
            $optionalAttrs = $property->getAttributes(Optional::class);

            if (count($optionalAttrs) > 0) {
                $optional = true;
            }

            // Resolve CastWith attribute
            $castWithAttrs = $property->getAttributes(CastWith::class);

            if (count($castWithAttrs) > 0) {
                $castWith = $castWithAttrs[0]->newInstance();
                $caster = new ($castWith->casterClass)(...$castWith->args);
            }

            // Resolve type information
            $type = $property->getType();

            if ($type instanceof ReflectionNamedType) {
                $typeName = $type->getName();
                $nullable = $type->allowsNull();
                $isBuiltin = $type->isBuiltin();
            }

            $resolved[$name] = [
                'property' => $property,
                'sourceKey' => $sourceKey,
                'optional' => $optional,
                'caster' => $caster,
                'typeName' => $typeName,
                'nullable' => $nullable,
                'hasDefault' => $hasDefault,
                'default' => $default,
                'isBuiltin' => $isBuiltin,
            ];
        }

        return $resolved;
    }
}
