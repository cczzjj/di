<?php

declare(strict_types=1);

namespace DI;

/**
 * Describe an injection in a class property.
 */
class PropertyInjection
{
    /**
     * @param string $propertyName Property name
     * @param string $targetEntryName Name of the target entry
     * @param string|null $className Use for injecting in properties of parent classes: the class name
     *                               must be the name of the parent class because private properties
     *                               can be attached to the parent classes, not the one we are resolving.
     */
    public function __construct(
        private string  $propertyName,
        private string  $targetEntryName,
        private ?string $className = null
    ) {
    }

    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    public function getTargetEntryName(): string
    {
        return $this->targetEntryName;
    }

    public function getClassName(): ?string
    {
        return $this->className;
    }
}
