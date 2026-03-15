# Changelog

All notable changes to `php-dto-mapper` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 2026-03-15

### Added
- Initial release
- `DtoMapper::map()` for array-to-DTO mapping
- `DtoMapper::mapJson()` for JSON-to-DTO mapping
- `DtoMapper::mapCollection()` for mapping arrays of arrays
- `DtoMapper::tryMap()` for safe mapping returning null on failure
- `#[MapFrom]` attribute for source key remapping
- `#[Optional]` attribute for nullable/default fields
- `#[CastWith]` attribute for custom type casting
- `Caster` interface for implementing custom casters
- `DateTimeCaster` for string-to-DateTimeImmutable conversion
- `EnumCaster` for string/int-to-backed-enum conversion
- Automatic type coercion for scalar types
- Nested DTO hydration support
- Readonly property support
- Constructor promotion support
