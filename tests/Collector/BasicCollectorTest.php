<?php

declare(strict_types=1);

namespace Clouding\Presto\Tests\Collector;

use Clouding\Presto\Collectors\BasicCollector;
use PHPUnit\Framework\TestCase;

class BasicCollectorTest extends TestCase
{
    public function testGet()
    {
        $collector = new BasicCollector();

        $this->assertEmpty($collector->get());
    }

    public function testCollect()
    {
        $object1 = (object) [
            'data' => [
                [1, 'Go to school']
            ]
        ];

        $object2 = (object) [
            'data' => [
                [2, 'Go to store']
            ]
        ];

        $collector = new BasicCollector();
        $collector->collect($object1);
        $collector->collect($object2);

        $expected = [
            [1, 'Go to school'],
            [2, 'Go to store'],
        ];
        $this->assertSame($expected, $collector->get());
    }

    public function testCollectNothing()
    {
        $collector = new BasicCollector();
        $collector->collect((object) ['banana']);

        $this->assertEmpty($collector->get());
    }
}
