<?php

declare(strict_types=1);

namespace Clouding\Presto\Tests\Collector;

use Clouding\Presto\Collectors\Collector;
use PHPUnit\Framework\TestCase;
use Tightenco\Collect\Support\Collection;

class CollectorTest extends TestCase
{
    public function testGet()
    {
        $collector = new Collector();

        $this->assertInstanceOf(Collection::class, $collector->get());
        $this->assertEmpty($collector->get()->toArray());
    }

    public function testCollect()
    {
        $collector = new Collector();
        $collector->collect((object) ['data' => 1]);
        $collector->collect((object) ['data' => 1]);

        $this->assertSame([1, 1], $collector->get()->toArray());
    }

    public function testCollectNothing()
    {
        $collector = new Collector();
        $collector->collect((object) ['banana']);

        $this->assertEmpty($collector->get()->toArray());
    }
}
