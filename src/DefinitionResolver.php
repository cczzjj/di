<?php

declare(strict_types=1);

namespace DI;

use DI\Exception\InvalidDefinitionException;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionProperty;

/**
 * Create objects based on a definition.
 */
class DefinitionResolver
{
    public function __construct(private ContainerInterface $container)
    {
    }

    /**
     * Resolve a class definition to a value.
     *
     * This will create a new instance of the class using the injections points defined.
     *
     * @param Definition $definition
     * @param array $parameters
     * @return mixed
     * @throws InvalidDefinitionException
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function resolve(Definition $definition, array $parameters = []): mixed
    {
        return $this->createInstance($definition, $parameters);
    }

    /**
     * The definition is not resolvable if the class is not instantiable (interface or abstract)
     * or if the class doesn't exist.
     *
     * @param Definition $definition
     * @param array $parameters
     * @return bool
     */
    public function isResolvable(Definition $definition, array $parameters = []): bool
    {
        return $definition->isInstantiable();
    }

    /**
     * Creates an instance of the class and injects dependencies..
     *
     * @param Definition $definition
     * @param array $parameters Optional parameters to use to create the instance.
     * @return mixed
     * @throws InvalidDefinitionException
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    private function createInstance(Definition $definition, array $parameters): mixed
    {
        // Check that the class is instantiable
        if (!$definition->isInstantiable()) {
            // Check that the class exists
            if (!$definition->classExists()) {
                throw new InvalidDefinitionException(sprintf(
                    'Entry "%s" cannot be resolved: the class doesn\'t exist',
                    $definition->getName()
                ));
            }

            throw new InvalidDefinitionException(sprintf(
                'Entry "%s" cannot be resolved: the class is not instantiable',
                $definition->getName()
            ));
        }

        /** @psalm-var class-string $classname */
        $classname = $definition->getClassName();

        $object = new $classname(...$parameters);

        $this->injectProperties($object, $definition);

        return $object;
    }

    /**
     * @param object $object
     * @param Definition $objectDefinition
     *
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    protected function injectProperties(object $object, Definition $objectDefinition): void
    {
        // Property injections
        foreach ($objectDefinition->getPropertyInjections() as $propertyInjection) {
            $this->injectProperty($object, $propertyInjection);
        }
    }

    /**
     * Inject dependencies into properties.
     *
     * @param object $object Object to inject dependencies into
     * @param PropertyInjection $propertyInjection Property injection definition
     *
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    private function injectProperty(object $object, PropertyInjection $propertyInjection): void
    {
        $propertyName = $propertyInjection->getPropertyName();

        $entryName = $propertyInjection->getTargetEntryName();

        $value = $this->container->get($entryName);

        self::setPrivatePropertyValue($propertyInjection->getClassName(), $object, $propertyName, $value);
    }

    /**
     * @throws \ReflectionException
     */
    public static function setPrivatePropertyValue(?string $className, $object, string $propertyName, object $propertyValue): void
    {
        $className = $className ?: get_class($object);

        $property = new ReflectionProperty($className, $propertyName);
        if (!$property->isPublic()) {
            $property->setAccessible(true);
        }
        $property->setValue($object, $propertyValue);
    }
}