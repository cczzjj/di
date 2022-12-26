<?php

declare(strict_types=1);

namespace DI;

use ReflectionClass;

/**
 * Defines how an object can be instantiated.
 */
class Definition
{
    /**
     * Entry name (most of the time, same as $classname).
     */
    private string $name;

    /**
     * Class name (if null, then the class name is $name).
     */
    protected ?string $className = null;

    /**
     * Method calls.
     * @var PropertyInjection[]
     */
    protected array $propertyInjections = [];

    /**
     * Store if the class exists. Storing it (in cache) avoids recomputing this.
     */
    private bool $classExists;

    /**
     * Store if the class is instantiable. Storing it (in cache) avoids recomputing this.
     */
    private bool $isInstantiable;

    /**
     * @param string $name Entry name
     * @param string|null $className Class name
     */
    public function __construct(string $name, string $className = null)
    {
        $this->name = $name;
        $this->setClassName($className);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setClassName(?string $className): void
    {
        $this->className = $className;

        $this->updateCache();
    }

    public function getClassName(): ?string
    {
        return $this->className ?? $this->name;
    }

    /**
     * @return PropertyInjection[] Property injections
     */
    public function getPropertyInjections(): array
    {
        return $this->propertyInjections;
    }

    public function addPropertyInjection(PropertyInjection $propertyInjection): void
    {
        $className = $propertyInjection->getClassName();
        if ($className) {
            // Index with the class name to avoid collisions between parent and
            // child private properties with the same name
            $key = $className . '::' . $propertyInjection->getPropertyName();
        } else {
            $key = $propertyInjection->getPropertyName();
        }

        $this->propertyInjections[$key] = $propertyInjection;
    }

    public function classExists(): bool
    {
        return $this->classExists;
    }

    public function isInstantiable(): bool
    {
        return $this->isInstantiable;
    }

    private function updateCache(): void
    {
        $className = $this->getClassName();

        $this->classExists = class_exists($className) || interface_exists($className);

        if (!$this->classExists) {
            $this->isInstantiable = false;

            return;
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        $class = new ReflectionClass($className);
        $this->isInstantiable = $class->isInstantiable();
    }
}