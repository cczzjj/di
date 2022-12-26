<?php

declare(strict_types=1);

namespace DI\Exception;

use Psr\Container\ContainerExceptionInterface;

/**
 * Error in using php attributes
 */
class AttributeException extends \Exception implements ContainerExceptionInterface
{
}