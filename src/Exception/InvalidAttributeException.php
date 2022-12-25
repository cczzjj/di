<?php

declare(strict_types=1);

namespace DI\Exception;

use Psr\Container\ContainerExceptionInterface;

/**
 * Error in the definitions using PHP attributes.
 */
class InvalidAttributeException extends \Exception implements ContainerExceptionInterface
{
}