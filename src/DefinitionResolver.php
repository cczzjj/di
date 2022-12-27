<?php

declare(strict_types=1);

namespace DI;

use Psr\Container\ContainerInterface;
use ReflectionProperty;

/**
 * Update object based on definition.
 */
class DefinitionResolver
{
    public function __construct(private ContainerInterface $container)
    {
    }

    /**
     * Resolve a class definition.
     *
     * @param mixed $object
     * @param Definition $definition
     * @return void
     */
    public function resolve(mixed $object, Definition $definition): void
    {
        $this->injectProperties($object, $definition);
    }

    protected function injectProperties(mixed $object, Definition $objectDefinition): void
    {
        foreach ($objectDefinition->getPropertyInjections() as $propertyInjection) {
            $this->injectProperty($object, $propertyInjection);
        }
    }

    /**
     * Inject dependencies into properties.
     *
     * @param mixed $object Object to inject dependencies into
     * @param PropertyInjection $propertyInjection Property injection definition
     */
    private function injectProperty(mixed $object, PropertyInjection $propertyInjection): void
    {
        $propertyName = $propertyInjection->getPropertyName();

        $entryName = $propertyInjection->getTargetEntryName();

        /** @noinspection PhpUnhandledExceptionInspection */
        $value = $this->container->get($entryName);

        self::setPrivatePropertyValue($propertyInjection->getClassName(), $object, $propertyName, $value);
    }

    public static function setPrivatePropertyValue(?string $className, mixed $object, string $propertyName, object $propertyValue): void
    {
        $className = $className ?: get_class($object);

        /** @noinspection PhpUnhandledExceptionInspection */
        $property = new ReflectionProperty($className, $propertyName);
        if (!$property->isPublic()) {
            $property->setAccessible(true);
        }
        $property->setValue($object, $propertyValue);
    }
}