<?php

declare(strict_types=1);

namespace Clouding\Presto\Tests;

use Clouding\Presto\Contracts\ProcessorInterface;
use Clouding\Presto\QueryBuilder;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class QueryBuilderTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testToSql()
    {
        $mockProcessor = Mockery::mock(ProcessorInterface::class);
        $builder = new QueryBuilder($mockProcessor);

        $query = 'Hi I am Corina';
        $builder->raw($query);

        $this->assertSame($query, $builder->toSql());
    }

    public function testGet()
    {
        $mockProcessor = Mockery::mock(ProcessorInterface::class);
        $mockProcessor->shouldReceive('handle')
            ->once()
            ->andReturn(collect([1, 2, 3]));

        $builder = new QueryBuilder($mockProcessor);
        $rows = $builder->get();

        $this->assertEquals(collect([1, 2, 3]), $rows);
    }
}
