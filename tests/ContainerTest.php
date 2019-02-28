<?php

declare(strict_types=1);

namespace Clouding\Presto\Tests;

use Clouding\Presto\Container;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    /**
     * @var \Clouding\Presto\Container
     */
    protected $container;

    public function setUp(): void
    {
        $this->container = new Container([1, 2, 3]);
    }

    public function testToArray()
    {
        $this->assertEquals([1, 2, 3], $this->container->toArray());
    }

    public function testOffsetExists()
    {
        $this->assertTrue(isset($this->container[0]));
    }

    public function testOffsetGet()
    {
        $this->assertSame(1, $this->container[0]);
    }

    public function testOffsetSet()
    {
        $this->container['foo'] = 'bar';

        $this->assertSame('bar', $this->container['foo']);
    }

    public function testUnset()
    {
        unset($this->container[0]);

        $this->assertFalse(isset($this->container[0]));
    }

    public function testGet()
    {
        $this->assertSame(1, $this->container->{0});
    }

    public function testSet()
    {
        $this->container->{0} = 'bar';

        $this->assertSame('bar', $this->container[0]);
    }
}
