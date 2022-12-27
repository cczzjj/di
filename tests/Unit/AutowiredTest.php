<?php

declare(strict_types=1);

namespace Tests\Unit;

use DI\Container;
use DI\Exception\AttributeException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Tests\Unit\Entity\Entity2;
use Tests\Unit\Entity\Entity1;
use Tests\Unit\Entity\Entity3;
use Tests\Unit\Entity\Entity4;
use Tests\Unit\Entity\Entity5;
use Tests\Unit\Entity\Entity6;

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
        $this->expectException(AttributeException::class);

        $this->container->get(Entity1::class);
        /** @var Entity3 $entity3 */
        $this->container->get(Entity3::class);
    }

    public function testCircularDependencies() {
        /** @var Entity4 $entity4 */
        $entity4 = $this->container->get(Entity4::class);
        /** @var Entity5 $entity5 */
        $entity5 = $this->container->get(Entity5::class);
        /** @var Entity6 $entity6 */
        $entity6 = $this->container->get(Entity6::class);

        $this->assertSame($entity4->getProperty5(), $entity5);
        $this->assertSame($entity4->getProperty6(), $entity6);
        $this->assertSame($entity5->getProperty4(), $entity4);
        $this->assertSame($entity6->getProperty4(), $entity4);
    }
}