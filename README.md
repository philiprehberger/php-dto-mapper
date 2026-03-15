# PHP DTO Mapper

[![Tests](https://github.com/philiprehberger/php-dto-mapper/actions/workflows/tests.yml/badge.svg)](https://github.com/philiprehberger/php-dto-mapper/actions/workflows/tests.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/philiprehberger/php-dto-mapper.svg)](https://packagist.org/packages/philiprehberger/php-dto-mapper)
[![Total Downloads](https://img.shields.io/packagist/dt/philiprehberger/php-dto-mapper.svg)](https://packagist.org/packages/philiprehberger/php-dto-mapper)
[![PHP Version Require](https://img.shields.io/packagist/php-v/philiprehberger/php-dto-mapper.svg)](https://packagist.org/packages/philiprehberger/php-dto-mapper)
[![License](https://img.shields.io/github/license/philiprehberger/php-dto-mapper)](LICENSE)

Map arrays and JSON to strongly-typed DTOs with attribute-driven configuration.

---

## Requirements

| Dependency | Version |
|------------|---------|
| PHP        | ^8.2    |

Zero external dependencies.

---

## Installation

```bash
composer require philiprehberger/php-dto-mapper
```

---

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

---

## API

| Method | Description |
|--------|-------------|
| `DtoMapper::map(array $data, string $class): object` | Map an associative array to a DTO |
| `DtoMapper::mapJson(string $json, string $class): object` | Map a JSON string to a DTO |
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

---

## Testing

```bash
composer install
vendor/bin/phpunit
```

Code style:

```bash
vendor/bin/pint
```

Static analysis:

```bash
vendor/bin/phpstan analyse
```

---

## Changelog

Please see [CHANGELOG.md](CHANGELOG.md) for recent changes.

---

## License

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.
