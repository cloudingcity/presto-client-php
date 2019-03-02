<?php

declare(strict_types=1);

namespace Clouding\Presto\Tests;

use Clouding\Presto\Collectors\AssocCollector;
use Clouding\Presto\Collectors\Collector;
use Clouding\Presto\Processor;
use Clouding\Presto\QueryBuilder;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Tightenco\Collect\Support\Collection;

class QueryBuilderTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testToSql()
    {
        $processor = mock(Processor::class);

        $builder = new QueryBuilder($processor);
        $builder->raw('Hi I am Corina');

        $this->assertSame('Hi I am Corina', $builder->toSql());
    }

    public function testGet()
    {
        $processor = mock(Processor::class);
        $processor->shouldReceive('execute')
            ->once()
            ->with('', Collector::class)
            ->andReturn(collect([1, 2, 3]));

        $builder = new QueryBuilder($processor);
        $rows = $builder->get();

        $this->assertInstanceOf(Collection::class, $rows);
        $this->assertEquals([1, 2, 3], $rows->toArray());
    }

    public function testGetAssoc()
    {
        $processor = mock(Processor::class);
        $processor->shouldReceive('execute')
            ->once()
            ->with('', AssocCollector::class)
            ->andReturn(collect([1, 2, 3]));

        $builder = new QueryBuilder($processor);
        $rows = $builder->getAssoc();

        $this->assertInstanceOf(Collection::class, $rows);
        $this->assertEquals([1, 2, 3], $rows->toArray());
    }
}
