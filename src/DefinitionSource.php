<?php

declare(strict_types=1);

namespace DI;

use DI\Attribute\Autowired;
use DI\Exception\AttributeException;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;

/**
 * Provides DI definitions by reading PHP8 attributes.
 */
class DefinitionSource
{
    /**
     * Returns the DI definition for the entry name.
     */
    public function getDefinition(string $name): ?Definition
    {
        return $this->autowire($name);
    }

    public function autowire(string $name, Definition $definition = null): Definition|null
    {
        $className = $definition ? $definition->getClassName() : $name;

        if (!class_exists($className) && !interface_exists($className)) {
            return $definition;
        }

        $definition = $definition ?: new Definition($name);

        /** @noinspection PhpUnhandledExceptionInspection */
        $class = new ReflectionClass($className);

        // Browse the class properties looking for properties with attributes.
        $this->readProperties($class, $definition);

        return $definition;
    }

    /**
     * Browse the class properties looking for properties with attributes.
     */
    private function readProperties(ReflectionClass $class, Definition $definition): void
    {
        foreach ($class->getProperties() as $property) {
            if ($property->isStatic()) {
                continue;
            }
            /** @noinspection PhpUnhandledExceptionInspection */
            $this->readProperty($property, $definition);
        }

        // Read also the *private* properties of the parent classes
        while ($class = $class->getParentClass()) {
            foreach ($class->getProperties(ReflectionProperty::IS_PRIVATE) as $property) {
                if ($property->isStatic()) {
                    continue;
                }
                /** @noinspection PhpUnhandledExceptionInspection */
                $this->readProperty($property, $definition, $class->getName());
            }
        }
    }

    private function readProperty(ReflectionProperty $property, Definition $definition, ?string $classname = null): void
    {
        // Look for #[Autowired] attribute
        $attribute = $property->getAttributes(Autowired::class)[0] ?? null;
        if (!$attribute) {
            return;
        }

        // Check the type of the property
        $propertyType = $property->getType();
        if (!$propertyType instanceof ReflectionNamedType) {
            /** @noinspection PhpUnhandledExceptionInspection */
            throw new AttributeException(sprintf(
                '#[Autowired] found on property %s::%s but no type declared',
                $property->getDeclaringClass()->getName(),
                $property->getName()
            ));
        }
        if (!class_exists($propertyType->getName()) && !interface_exists($propertyType->getName())) {
            /** @noinspection PhpUnhandledExceptionInspection */
            throw new AttributeException(sprintf(
                '#[Autowired] found on property %s::%s but not a valid class or interface name',
                $property->getDeclaringClass()->getName(),
                $property->getName()
            ));
        }

        $entryName = $propertyType->getName();

        $definition->addPropertyInjection(
            new PropertyInjection($property->getName(), $entryName, $classname)
        );
    }
}