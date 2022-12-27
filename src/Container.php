<?php

declare(strict_types=1);

namespace DI;

use DI\Exception\DefinitionException;
use DI\Exception\NotFoundException;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    /**
     * Map of entries that are already resolved.
     *
     * @var array<string, mixed>
     */
    protected array $resolvedEntries = [];

    /**
     * Map of entries that are not fully resolved (L2 cache).
     *
     * @var array<string, mixed>
     */
    private array $earlyResolvedEntries = [];

    private DefinitionSource $definitionSource;

    private DefinitionResolver $definitionResolver;

    /**
     * Map of definitions that are already fetched (local cache).
     *
     * @var array<string, Definition|null>
     */
    private array $fetchedDefinitions = [];

    public function __construct()
    {
        $this->definitionSource = new DefinitionSource();
        $this->definitionResolver = new DefinitionResolver($this);

        // Auto-register the container
        $this->resolvedEntries = [
            self::class => $this,
            ContainerInterface::class => $this,
        ];
    }

    /**
     * Return an entry from the container by its name.
     *
     * @param string $id Entry name or a class name.
     * @return mixed
     * @throws DefinitionException Class does not exist or is not instantiable.
     * @throws NotFoundException No entry found for the given name.
     */
    public function get(string $id): mixed
    {
        // If the entry is already resolved we return it
        if (isset($this->resolvedEntries[$id]) || array_key_exists($id, $this->resolvedEntries)) {
            return $this->resolvedEntries[$id];
        }

        $definition = $this->getDefinition($id);
        if (!$definition) {
            throw new NotFoundException("No entry or class found for '$id'");
        }

        if (isset($this->earlyResolvedEntries[$id]) || array_key_exists($id, $this->earlyResolvedEntries)) {
            return $this->earlyResolvedEntries[$id];
        } else {
            $this->createEarlyInstance($definition);
        }

        return $this->resolveDefinition($definition);
    }

    private function getDefinition(string $name): ?Definition
    {
        // Local cache that avoids fetching the same definition twice
        if (!array_key_exists($name, $this->fetchedDefinitions)) {
            $this->fetchedDefinitions[$name] = $this->definitionSource->getDefinition($name);
        }

        return $this->fetchedDefinitions[$name];
    }

    /**
     * Build an entry of the container by its name.
     *
     * This method behave like get() except resolves the entry again every time.
     * For example if the entry is a class then a new instance will be created each time.
     *
     * This method makes the container behave like a factory.
     *
     * @param string|class-string $name Entry name or a class name.
     * @param array $parameters Optional parameters to use to build the entry. Use this to force
     *                          specific parameters to specific values. Parameters not defined in this
     *                          array will be resolved using the container.
     *
     * @throws DefinitionException Class does not exist or is not instantiable
     * @throws NotFoundException No entry found for the given name.
     */
    public function make(string $name, array $parameters = []): object
    {
        $definition = $this->getDefinition($name);
        if (!$definition) {
            // If the entry is already resolved we return it
            if (array_key_exists($name, $this->resolvedEntries)) {
                return $this->resolvedEntries[$name];
            }

            throw new NotFoundException("No entry or class found for '$name'");
        }

        if (isset($this->earlyResolvedEntries[$name]) || array_key_exists($name, $this->earlyResolvedEntries)) {
            return $this->earlyResolvedEntries[$name];
        } else {
            $this->createEarlyInstance($definition, $parameters);
        }

        return $this->resolveDefinition($definition);
    }

    /**
     * {@inheritDoc}
     */
    public function has(string $id): bool
    {
        return array_key_exists($id, $this->resolvedEntries);
    }

    /**
     * Define an object in the container.
     *
     * @param string $name Entry name
     * @param mixed $value Value
     */
    public function set(string $name, mixed $value): void
    {
        $this->resolvedEntries[$name] = $value;
    }

    /**
     * Create a not fully resolved entry.
     *
     * @throws DefinitionException Class does not exist or is not instantiable.
     */
    private function createEarlyInstance(Definition $definition, array $parameters = []): void
    {
        // Check that the class is instantiable
        if (!$definition->isInstantiable()) {
            // Check that the class exists
            if (!$definition->classExists()) {
                throw new DefinitionException(sprintf(
                    'Entry "%s" cannot be resolved: the class doesn\'t exist',
                    $definition->getName()
                ));
            }

            throw new DefinitionException(sprintf(
                'Entry "%s" cannot be resolved: the class is not instantiable',
                $definition->getName()
            ));
        }

        $classname = $definition->getClassName();

        $this->earlyResolvedEntries[$classname] = new $classname(...$parameters);
    }

    /**
     * Resolve a definition.
     *
     * @param Definition $definition
     * @return mixed
     */
    private function resolveDefinition(Definition $definition): mixed
    {
        $entryName = $definition->getName();

        $earlyEntry = $this->earlyResolvedEntries[$entryName];

        $this->definitionResolver->resolve($earlyEntry, $definition);

        $this->resolvedEntries[$entryName] = $earlyEntry;

        return $earlyEntry;
    }
}