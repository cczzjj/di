<?php

declare(strict_types=1);

namespace DI\Exception;

use Psr\Container\ContainerExceptionInterface;

/**
 * Exception for DI definitions.
 */
class DefinitionException extends \Exception implements ContainerExceptionInterface
{
}