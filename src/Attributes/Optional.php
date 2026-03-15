<?php

declare(strict_types=1);

namespace PhilipRehberger\DtoMapper\Attributes;

use Attribute;

/**
 * Marks a property as optional, allowing missing keys without error.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Optional {}
