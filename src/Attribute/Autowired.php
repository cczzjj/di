<?php

declare(strict_types=1);

namespace DI\Attribute;

use Attribute;

/**
 * #[Autowired] attribute.
 *
 * Marks a property as an injection point
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class Autowired
{
}
