<?php

declare(strict_types=1);

namespace Tests\Unit;

use DI\Container;
use DI\Exception\InvalidAttributeException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Tests\Unit\Entity\Entity2;
use Tests\Unit\Entity\Entity1;
use Tests\Unit\Entity\Entity3;

class AutowiredTest extends TestCase
{
    private ReflectionClass $reflectionClass;

    private Container $container;

    protected function setUp(): void
    {
        $this->reflectionClass = new ReflectionClass(Entity2::class);
        $this->container = new Container;
    }

    public function testProperty1()
    {
        $entity1 = $this->container->get(Entity1::class);
        /** @var Entity2 $entity2 */
        $entity2 = $this->container->get(Entity2::class);

        $this->assertSame($entity1, $entity2->getProperty1());
    }

    public function testProperty2()
    {
        $this->expectException(InvalidAttributeException::class);

        $this->container->get(Entity1::class);
        /** @var Entity3 $entity3 */
        $this->container->get(Entity3::class);
    }
}