# PHP DTO Mapper

[![Tests](https://github.com/philiprehberger/php-dto-mapper/actions/workflows/tests.yml/badge.svg)](https://github.com/philiprehberger/php-dto-mapper/actions/workflows/tests.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/philiprehberger/php-dto-mapper.svg)](https://packagist.org/packages/philiprehberger/php-dto-mapper)
[![License](https://img.shields.io/github/license/philiprehberger/php-dto-mapper)](LICENSE)

Map arrays and JSON to strongly-typed DTOs with attribute-driven configuration.

## Requirements

- PHP 8.2+

## Installation

```bash
composer require philiprehberger/php-dto-mapper
```

## Usage

### Define a DTO

```php
use PhilipRehberger\DtoMapper\Attributes\MapFrom;
use PhilipRehberger\DtoMapper\Attributes\Optional;
use PhilipRehberger\DtoMapper\Attributes\CastWith;
use PhilipRehberger\DtoMapper\Casters\DateTimeCaster;

class UserDto
{
    public function __construct(
        public readonly string $name,
        #[MapFrom('email_address')]
        public readonly string $email,
        #[Optional]
        public readonly ?string $nickname = null,
        #[CastWith(DateTimeCaster::class)]
        public readonly ?\DateTimeImmutable $createdAt = null,
    ) {}
}
```

### Map from array

```php
use PhilipRehberger\DtoMapper\DtoMapper;

$dto = DtoMapper::map([
    'name' => 'John',
    'email_address' => 'john@example.com',
    'createdAt' => '2026-01-15 10:30:00',
], UserDto::class);

$dto->name;      // 'John'
$dto->email;     // 'john@example.com'
$dto->createdAt; // DateTimeImmutable
```

### Map from JSON

```php
$dto = DtoMapper::mapJson('{"name": "Jane", "email_address": "jane@example.com"}', UserDto::class);
```

### Map a collection

```php
$dtos = DtoMapper::mapCollection([
    ['name' => 'Alice', 'email_address' => 'alice@example.com'],
    ['name' => 'Bob', 'email_address' => 'bob@example.com'],
], UserDto::class);
```

### Safe mapping

```php
$dto = DtoMapper::tryMap($data, UserDto::class); // Returns null on failure
```

### Nested DTOs

```php
class AddressDto
{
    public function __construct(
        public readonly string $street,
        public readonly string $city,
    ) {}
}

class PersonDto
{
    public function __construct(
        public readonly string $name,
        public readonly AddressDto $address,
    ) {}
}

$dto = DtoMapper::map([
    'name' => 'Alice',
    'address' => ['street' => '123 Main St', 'city' => 'Springfield'],
], PersonDto::class);

$dto->address->city; // 'Springfield'
```

### Partial mapping

Map incomplete data without errors for missing non-nullable fields:

```php
class ProfileDto
{
    public function __construct(
        public readonly string $name,
        public readonly int $age,
        public readonly ?string $bio = null,
        public readonly string $role = 'member',
    ) {}
}

$dto = DtoMapper::mapPartial([
    'name' => 'Alice',
], ProfileDto::class);

$dto->name; // 'Alice'
$dto->bio;  // null (nullable gets null)
$dto->role; // 'member' (default preserved)
// $dto->age is not set (non-nullable without default, skipped)
```

### Union types

Properties with union types are coerced by trying each type in declaration order:

```php
class EventDto
{
    public function __construct(
        public readonly string $name,
        public readonly int|string $identifier,
    ) {}
}

$dto = DtoMapper::map(['name' => 'Login', 'identifier' => '99'], EventDto::class);
$dto->identifier; // 99 (coerced to int, the first type)

$dto = DtoMapper::map(['name' => 'Login', 'identifier' => 'abc-123'], EventDto::class);
$dto->identifier; // 'abc-123' (kept as string)
```

### Custom casters

Implement the `Caster` interface:

```php
use PhilipRehberger\DtoMapper\Contracts\Caster;

class MoneyFromCentsCaster implements Caster
{
    public function cast(mixed $value): float
    {
        return (int) $value / 100;
    }
}
```

Use with the `#[CastWith]` attribute:

```php
class OrderDto
{
    public function __construct(
        #[CastWith(MoneyFromCentsCaster::class)]
        public readonly float $total,
    ) {}
}
```

### Enum casting

```php
use PhilipRehberger\DtoMapper\Attributes\CastWith;
use PhilipRehberger\DtoMapper\Casters\EnumCaster;

enum Status: string
{
    case Active = 'active';
    case Inactive = 'inactive';
}

class AccountDto
{
    public function __construct(
        public readonly string $name,
        #[CastWith(EnumCaster::class, args: [Status::class])]
        public readonly Status $status,
    ) {}
}
```

## API

| Method | Description |
|--------|-------------|
| `DtoMapper::map(array $data, string $class): object` | Map an associative array to a DTO |
| `DtoMapper::mapJson(string $json, string $class): object` | Map a JSON string to a DTO |
| `DtoMapper::mapPartial(array $data, string $class): object` | Map without requiring all fields |
| `DtoMapper::mapCollection(array $items, string $class): array` | Map an array of arrays to DTOs |
| `DtoMapper::tryMap(array $data, string $class): ?object` | Map returning null on failure |

### Attributes

| Attribute | Target | Description |
|-----------|--------|-------------|
| `#[MapFrom('key')]` | Property | Map from a different source key |
| `#[Optional]` | Property | Allow missing keys, use default value |
| `#[CastWith(Caster::class)]` | Property | Apply a custom caster |

### Built-in Casters

| Caster | Description |
|--------|-------------|
| `DateTimeCaster` | Casts string to `DateTimeImmutable` |
| `EnumCaster` | Casts string/int to a backed enum |

## Development

```bash
composer install
vendor/bin/phpunit
vendor/bin/pint --test
vendor/bin/phpstan analyse
```

## License

MIT
