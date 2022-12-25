<?php

declare(strict_types=1);

namespace DI\Exception;

use Psr\Container\ContainerExceptionInterface;

/**
 * Invalid DI definitions.
 */
class InvalidDefinitionException extends \Exception implements ContainerExceptionInterface
{
}