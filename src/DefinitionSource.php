<?php

declare(strict_types=1);

namespace DI;

use DI\Attribute\Autowired;
use DI\Exception\InvalidAttributeException;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;
use Throwable;

/**
 * Provides DI definitions by reading PHP 8 attributes.
 *
 * This source automatically includes the reflection source.
 */
class DefinitionSource
{
    /**
     * @throws InvalidAttributeException
     * @throws \ReflectionException
     */
    public function autowire(string $name, Definition $definition = null): Definition|null
    {
        $className = $definition ? $definition->getClassName() : $name;

        if (!class_exists($className) && !interface_exists($className)) {
            return $definition;
        }

        $definition = $definition ?: new Definition($name);

        $class = new ReflectionClass($className);

        // Browse the class properties looking for annotated properties
        $this->readProperties($class, $definition);

        return $definition;
    }

    /**
     * Returns the DI definition for the entry name.
     *
     * @throws InvalidAttributeException
     * @throws \ReflectionException
     */
    public function getDefinition(string $name): ?Definition
    {
        return $this->autowire($name);
    }

    /**
     * Browse the class properties looking for annotated properties.
     *
     * @throws InvalidAttributeException
     */
    private function readProperties(ReflectionClass $class, Definition $definition): void
    {
        foreach ($class->getProperties() as $property) {
            if ($property->isStatic()) {
                continue;
            }
            $this->readProperty($property, $definition);
        }

        // Read also the *private* properties of the parent classes
        /** @noinspection PhpAssignmentInConditionInspection */
        while ($class = $class->getParentClass()) {
            foreach ($class->getProperties(ReflectionProperty::IS_PRIVATE) as $property) {
                if ($property->isStatic()) {
                    continue;
                }
                $this->readProperty($property, $definition, $class->getName());
            }
        }
    }

    /**
     * @throws InvalidAttributeException
     */
    private function readProperty(ReflectionProperty $property, Definition $definition, ?string $classname = null): void
    {
        // Look for #[Autowired] attribute
        try {
            $attribute = $property->getAttributes(Autowired::class)[0] ?? null;
            if (!$attribute) {
                return;
            }
        } catch (Throwable $e) {
            throw new InvalidAttributeException(sprintf(
                '#[Autowired] annotation on property %s::%s is malformed. %s',
                $property->getDeclaringClass()->getName(),
                $property->getName(),
                $e->getMessage()
            ), 0, $e);
        }

        // Try using typed properties
        $propertyType = $property->getType();
        if (!$propertyType instanceof ReflectionNamedType) {
            throw new InvalidAttributeException(sprintf(
                '#[Autowired] found on property %s::%s but undeclared variable type',
                $property->getDeclaringClass()->getName(),
                $property->getName()
            ));
        }
        if (!class_exists($propertyType->getName()) && !interface_exists($propertyType->getName())) {
            throw new InvalidAttributeException(sprintf(
                '#[Autowired] found on property %s::%s but unable to guess what to inject, the type of the property does not look like a valid class or interface name',
                $property->getDeclaringClass()->getName(),
                $property->getName()
            ));
        }

        $entryName = $propertyType->getName();
        if ($entryName === null) {
            throw new InvalidAttributeException(sprintf(
                '#[Autowired] found on property %s::%s but unable to guess what to inject, please add a type to the property',
                $property->getDeclaringClass()->getName(),
                $property->getName()
            ));
        }

        $definition->addPropertyInjection(
            new PropertyInjection($property->getName(), $entryName, $classname)
        );
    }
}