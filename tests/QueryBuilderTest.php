<?php

declare(strict_types=1);

namespace Clouding\Presto\Tests;

use Clouding\Presto\Collectors\AssocCollector;
use Clouding\Presto\Collectors\BasicCollector;
use Clouding\Presto\Processor;
use Clouding\Presto\QueryBuilder;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

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
            ->with('', BasicCollector::class)
            ->andReturn([1, 2, 3]);

        $builder = new QueryBuilder($processor);
        $rows = $builder->get();

        $this->assertEquals([1, 2, 3], $rows);
    }

    public function testGetAssoc()
    {
        $processor = mock(Processor::class);
        $processor->shouldReceive('execute')
            ->once()
            ->with('', AssocCollector::class)
            ->andReturn([1, 2, 3]);

        $builder = new QueryBuilder($processor);
        $rows = $builder->getAssoc();

        $this->assertEquals([1, 2, 3], $rows);
    }
}
