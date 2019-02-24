<?php

declare(strict_types=1);

namespace Clouding\Presto\Tests;

use Clouding\Presto\Processor;
use Clouding\Presto\QueryBuilder;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Tightenco\Collect\Support\Collection;

class QueryBuilderTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testToSql()
    {
        $mockProcessor = Mockery::mock(Processor::class);
        $builder = new QueryBuilder($mockProcessor);

        $query = 'Hi I am Corina';
        $builder->raw($query);

        $this->assertSame($query, $builder->toSql());
    }

    public function testGet()
    {
        $mockProcessor = Mockery::mock(Processor::class);
        $mockProcessor->shouldReceive('execute')
            ->once()
            ->andReturn(collect([1, 2, 3]));

        $builder = new QueryBuilder($mockProcessor);
        $rows = $builder->get();

        $this->assertInstanceOf(Collection::class, $rows);
        $this->assertEquals([1, 2, 3], $rows->toArray());
    }

    public function testGetAssoc()
    {
        $mockProcessor = Mockery::mock(Processor::class);
        $mockProcessor->shouldReceive('setCollectAssoc')
            ->once();
        $mockProcessor->shouldReceive('execute')
            ->once()
            ->andReturn(collect([1, 2, 3]));

        $builder = new QueryBuilder($mockProcessor);
        $rows = $builder->getAssoc();

        $this->assertInstanceOf(Collection::class, $rows);
        $this->assertEquals([1, 2, 3], $rows->toArray());
    }
}
