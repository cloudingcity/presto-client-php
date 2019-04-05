<?php

declare(strict_types=1);

namespace Clouding\Presto\Tests\Collector;

use Clouding\Presto\Collectors\AssocCollector;
use PHPUnit\Framework\TestCase;
use stdClass;
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
        $object = new stdClass();
        $object->columns = [['name' => 'id']];
        $object->data = [[1]];

        $object2 = new stdClass();
        $object2->columns = [['name' => 'id']];
        $object2->data = [[999]];

        $collector = new AssocCollector();
        $collector->collect($object);
        $collector->collect($object2);

        $this->assertSame([['id' => 1], ['id' => 999]], $collector->get()->toArray());
    }

    public function testCollectNothing()
    {
        $collector = new AssocCollector();
        $collector->collect((object) ['apple']);

        $this->assertEmpty($collector->get()->toArray());
    }
}
