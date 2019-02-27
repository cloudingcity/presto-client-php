<?php

declare(strict_types=1);

namespace Clouding\Presto\Tests\Collector;

use Clouding\Presto\Collectors\AssocCollector;
use PHPUnit\Framework\TestCase;
use Tightenco\Collect\Support\Collection;

class AssocCollectorTest extends TestCase
{
    public function testGet()
    {
        $collector = new AssocCollector();

        $this->assertInstanceOf(Collection::class, $collector->get());
        $this->assertEmpty($collector->get()->toArray());
    }

    public function testCollect()
    {
        $array1 = [
            'columns' => [
                [
                    'name' => 'id'
                ]
            ],
            'data' => [
                [
                    1
                ]
            ]
        ];
        $array2 = [
            'columns' => [
                [
                    'name' => 'id'
                ]
            ],
            'data' => [
                [
                    999
                ]
            ]
        ];

        $object1 = json_decode(json_encode($array1));
        $object2 = json_decode(json_encode($array2));

        $collector = new AssocCollector();
        $collector->collect($object1);
        $collector->collect($object2);

        $this->assertSame([['id' => 1], ['id' => 999]], $collector->get()->toArray());
    }

    public function testCollectNothing()
    {
        $collector = new AssocCollector();
        $collector->collect((object)['apple']);

        $this->assertEmpty($collector->get()->toArray());
    }
}
