<?php

declare(strict_types=1);

namespace Tests\Unit;

use DI\Container;
use DI\Exception\NotFoundException;
use PHPUnit\Framework\TestCase;
use stdClass;

class ContainerTest extends TestCase
{
    public function testBuilt()
    {
        self::assertInstanceOf(Container::class, new Container);
    }

    public function testSetGet()
    {
        $container = new Container;

        $object = new stdClass;
        $container->set('key1', $object);

        $this->assertSame($object, $container->get('key1'));

        $str = 'test1';
        $container->set('key2', $str);

        $this->assertSame($str, $container->get('key2'));
    }

    public function testNotFound()
    {
        $this->expectException(NotFoundException::class);

        $container = new Container;
        $container->get('key');
    }

    public function testGetWithClassName()
    {
        $container = new Container;

        $this->assertNotNull($container->get('stdClass'));
        $this->assertInstanceOf('stdClass', $container->get('stdClass'));
    }

    public function testGetResolvesEntryOnce()
    {
        $container = new Container;

        $this->assertSame($container->get('stdClass'), $container->get('stdClass'));
    }
}