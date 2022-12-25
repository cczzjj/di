<?php

declare(strict_types=1);

namespace DI;

/**
 * Describe an injection in a class property.
 */
class PropertyInjection
{
    private string $propertyName;

    /**
     * Name of the target entry
     */
    private string $targetEntryName;

    /**
     * Use for injecting in properties of parent classes: the class name
     * must be the name of the parent class because private properties
     * can be attached to the parent classes, not the one we are resolving.
     */
    private ?string $className;

    /**
     * @param string $propertyName Property name
     * @param string $targetEntryName Value that should be injected in the property
     * @param string|null $className
     */
    public function __construct(string $propertyName, string $targetEntryName, string $className = null)
    {
        $this->propertyName = $propertyName;
        $this->targetEntryName = $targetEntryName;
        $this->className = $className;
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
