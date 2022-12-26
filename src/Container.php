<?php

declare(strict_types=1);

namespace DI;

use DI\Exception\DependencyException;
use DI\Exception\NotFoundException;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    /**
     * Map of entries that are already resolved.
     */
    protected array $resolvedEntries = [];

    private DefinitionSource $definitionSource;

    private DefinitionResolver $definitionResolver;

    /**
     * Map of definitions that are already fetched (local cache).
     *
     * @var array<Definition|null>
     */
    private array $fetchedDefinitions = [];

    /**
     * Array of entries being resolved. Used to avoid circular dependencies and infinite loops.
     */
    protected array $entriesBeingResolved = [];

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
     * Returns an entry of the container by its name.
     *
     * @param string $id Entry name or a class name.
     * @return mixed
     * @throws DependencyException Error while resolving the entry.
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

        $value = $this->resolveDefinition($definition);

        $this->resolvedEntries[$id] = $value;

        return $value;
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
     * @throws DependencyException Error while resolving the entry.
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

        return $this->resolveDefinition($definition, $parameters);
    }

    /**
     * {@inheritDoc}
     */
    public function has(string $id): bool
    {
        if (array_key_exists($id, $this->resolvedEntries)) {
            return true;
        }

        $definition = $this->getDefinition($id);
        if ($definition === null) {
            return false;
        }

        return $this->definitionResolver->isResolvable($definition);
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
     * Resolves a definition.
     *
     * Checks for circular dependencies while resolving the definition.
     *
     * @param Definition $definition
     * @param array $parameters
     * @return mixed
     * @throws DependencyException Error while resolving the entry.
     */
    private function resolveDefinition(Definition $definition, array $parameters = []): mixed
    {
        $entryName = $definition->getName();

        // Check if we are already getting this entry -> circular dependency
        if (isset($this->entriesBeingResolved[$entryName])) {
            throw new DependencyException("Circular dependency detected while trying to resolve entry '$entryName'");
        }
        $this->entriesBeingResolved[$entryName] = true;

        // Resolve the definition
        try {
            /** @noinspection PhpUnhandledExceptionInspection */
            $value = $this->definitionResolver->resolve($definition, $parameters);
        } finally {
            unset($this->entriesBeingResolved[$entryName]);
        }

        return $value;
    }
}