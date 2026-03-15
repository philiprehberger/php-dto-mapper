<?php

declare(strict_types=1);

namespace PhilipRehberger\DtoMapper\Tests;

use DateTimeImmutable;
use PhilipRehberger\DtoMapper\DtoMapper;
use PhilipRehberger\DtoMapper\Exceptions\MappingException;
use PhilipRehberger\DtoMapper\Tests\Fixtures\AllDefaultsDto;
use PhilipRehberger\DtoMapper\Tests\Fixtures\CoercionDto;
use PhilipRehberger\DtoMapper\Tests\Fixtures\DateDto;
use PhilipRehberger\DtoMapper\Tests\Fixtures\DeeplyNestedDto;
use PhilipRehberger\DtoMapper\Tests\Fixtures\EnumDto;
use PhilipRehberger\DtoMapper\Tests\Fixtures\MappedDto;
use PhilipRehberger\DtoMapper\Tests\Fixtures\NestedDto;
use PhilipRehberger\DtoMapper\Tests\Fixtures\OptionalDto;
use PhilipRehberger\DtoMapper\Tests\Fixtures\SimpleDto;
use PhilipRehberger\DtoMapper\Tests\Fixtures\Status;
use PHPUnit\Framework\TestCase;

class DtoMapperTest extends TestCase
{
    public function test_simple_flat_dto_mapping(): void
    {
        $dto = DtoMapper::map([
            'name' => 'John',
            'age' => 30,
            'email' => 'john@example.com',
        ], SimpleDto::class);

        $this->assertInstanceOf(SimpleDto::class, $dto);
        $this->assertSame('John', $dto->name);
        $this->assertSame(30, $dto->age);
        $this->assertSame('john@example.com', $dto->email);
    }

    public function test_map_from_renames_source_key(): void
    {
        $dto = DtoMapper::map([
            'full_name' => 'Jane Doe',
            'user_email' => 'jane@example.com',
        ], MappedDto::class);

        $this->assertSame('Jane Doe', $dto->name);
        $this->assertSame('jane@example.com', $dto->email);
    }

    public function test_nested_dto_hydration(): void
    {
        $dto = DtoMapper::map([
            'name' => 'Alice',
            'address' => [
                'street' => '123 Main St',
                'city' => 'Springfield',
                'zip' => '62701',
            ],
        ], NestedDto::class);

        $this->assertSame('Alice', $dto->name);
        $this->assertSame('123 Main St', $dto->address->street);
        $this->assertSame('Springfield', $dto->address->city);
        $this->assertSame('62701', $dto->address->zip);
    }

    public function test_type_coercion_string_to_int(): void
    {
        $dto = DtoMapper::map([
            'count' => '42',
            'price' => '19.99',
            'active' => 'true',
        ], CoercionDto::class);

        $this->assertSame(42, $dto->count);
    }

    public function test_type_coercion_string_to_float(): void
    {
        $dto = DtoMapper::map([
            'count' => '5',
            'price' => '19.99',
            'active' => '1',
        ], CoercionDto::class);

        $this->assertSame(19.99, $dto->price);
    }

    public function test_type_coercion_string_to_bool(): void
    {
        $dto = DtoMapper::map([
            'count' => '1',
            'price' => '0.0',
            'active' => 'true',
        ], CoercionDto::class);

        $this->assertTrue($dto->active);

        $dto2 = DtoMapper::map([
            'count' => '0',
            'price' => '0.0',
            'active' => 'false',
        ], CoercionDto::class);

        $this->assertFalse($dto2->active);
    }

    public function test_optional_field_with_missing_key_uses_default(): void
    {
        $dto = DtoMapper::map([
            'name' => 'Bob',
        ], OptionalDto::class);

        $this->assertSame('Bob', $dto->name);
        $this->assertSame('none', $dto->nickname);
        $this->assertNull($dto->age);
    }

    public function test_required_field_missing_throws_mapping_exception(): void
    {
        $this->expectException(MappingException::class);

        DtoMapper::map([], SimpleDto::class);
    }

    public function test_mapping_exception_contains_all_errors(): void
    {
        try {
            DtoMapper::map([], SimpleDto::class);
            $this->fail('Expected MappingException was not thrown.');
        } catch (MappingException $e) {
            $this->assertCount(3, $e->errors);
            $this->assertStringContainsString('name', $e->errors[0]);
            $this->assertStringContainsString('age', $e->errors[1]);
            $this->assertStringContainsString('email', $e->errors[2]);
        }
    }

    public function test_cast_with_date_time_caster(): void
    {
        $dto = DtoMapper::map([
            'label' => 'Event',
            'createdAt' => '2026-01-15 10:30:00',
        ], DateDto::class);

        $this->assertInstanceOf(DateTimeImmutable::class, $dto->createdAt);
        $this->assertSame('2026-01-15', $dto->createdAt->format('Y-m-d'));
    }

    public function test_cast_with_enum_caster(): void
    {
        $dto = DtoMapper::map([
            'name' => 'User',
            'status' => 'active',
        ], EnumDto::class);

        $this->assertSame(Status::Active, $dto->status);
    }

    public function test_map_json_with_valid_json(): void
    {
        $json = '{"name": "Charlie", "age": 25, "email": "charlie@example.com"}';

        $dto = DtoMapper::mapJson($json, SimpleDto::class);

        $this->assertSame('Charlie', $dto->name);
        $this->assertSame(25, $dto->age);
    }

    public function test_map_json_with_invalid_json_throws(): void
    {
        $this->expectException(MappingException::class);

        DtoMapper::mapJson('not valid json', SimpleDto::class);
    }

    public function test_map_collection(): void
    {
        $items = [
            ['name' => 'Alice', 'age' => 30, 'email' => 'alice@example.com'],
            ['name' => 'Bob', 'age' => 25, 'email' => 'bob@example.com'],
            ['name' => 'Charlie', 'age' => 35, 'email' => 'charlie@example.com'],
        ];

        $dtos = DtoMapper::mapCollection($items, SimpleDto::class);

        $this->assertCount(3, $dtos);
        $this->assertSame('Alice', $dtos[0]->name);
        $this->assertSame('Bob', $dtos[1]->name);
        $this->assertSame('Charlie', $dtos[2]->name);
    }

    public function test_try_map_returns_null_on_failure(): void
    {
        $result = DtoMapper::tryMap([], SimpleDto::class);

        $this->assertNull($result);
    }

    public function test_try_map_returns_dto_on_success(): void
    {
        $result = DtoMapper::tryMap([
            'name' => 'Dave',
            'age' => 40,
            'email' => 'dave@example.com',
        ], SimpleDto::class);

        $this->assertInstanceOf(SimpleDto::class, $result);
        $this->assertSame('Dave', $result->name);
    }

    public function test_readonly_properties_supported(): void
    {
        $dto = DtoMapper::map([
            'name' => 'Eve',
            'age' => 28,
            'email' => 'eve@example.com',
        ], SimpleDto::class);

        $reflection = new \ReflectionProperty($dto, 'name');
        $this->assertTrue($reflection->isReadOnly());
        $this->assertSame('Eve', $dto->name);
    }

    public function test_constructor_promotion_supported(): void
    {
        $dto = DtoMapper::map([
            'name' => 'Frank',
            'age' => 33,
            'email' => 'frank@example.com',
        ], SimpleDto::class);

        $this->assertSame('Frank', $dto->name);
        $this->assertSame(33, $dto->age);
        $this->assertSame('frank@example.com', $dto->email);
    }

    public function test_deeply_nested_dtos(): void
    {
        $dto = DtoMapper::map([
            'name' => 'Grace',
            'address' => [
                'street' => '456 Oak Ave',
                'city' => 'Portland',
                'country' => [
                    'name' => 'United States',
                    'code' => 'US',
                ],
            ],
        ], DeeplyNestedDto::class);

        $this->assertSame('Grace', $dto->name);
        $this->assertSame('456 Oak Ave', $dto->address->street);
        $this->assertSame('Portland', $dto->address->city);
        $this->assertSame('United States', $dto->address->country->name);
        $this->assertSame('US', $dto->address->country->code);
    }

    public function test_empty_array_maps_to_dto_with_all_defaults(): void
    {
        $dto = DtoMapper::map([], AllDefaultsDto::class);

        $this->assertSame('default', $dto->name);
        $this->assertSame(0, $dto->count);
        $this->assertFalse($dto->enabled);
    }
}
