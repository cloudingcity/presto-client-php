<?php

declare(strict_types=1);

namespace Clouding\Presto\Tests;

use BlastCloud\Guzzler\UsesGuzzler;
use Clouding\Presto\Connection\Connection;
use Clouding\Presto\Contracts\Collectorable;
use Clouding\Presto\Exceptions\PrestoException;
use Clouding\Presto\Processor;
use GuzzleHttp\Psr7\Response;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Tightenco\Collect\Support\Collection;

class ProcessorTest extends TestCase
{
    use MockeryPHPUnitIntegration;
    use UsesGuzzler;

    public function testExecuteException()
    {
        $connection = $this->createConnection();

        $response = [
            'stats' => [
                'state' => Processor::FAILED
            ],
            'error' => [
                'message' => 'This is a error message',
                'errorName' => 'This is a error name',
            ]
        ];
        $this->guzzler->queueResponse(
            new Response(200, [], json_encode($response))
        );

        $collector = mock(Collectorable::class);


        $this->expectException(PrestoException::class);
        $this->expectExceptionMessage("{$response['error']['errorName']}: {$response['error']['message']}");

        $processor = new Processor($connection, $this->guzzler->getClient());
        $processor->execute('Go to school', $collector);
    }

    public function testExecute()
    {
        $connection = $this->createConnection();

        $responses = [
            [
                'nextUri' => 'http://example.com/1',
                'data' => [1, 2, 3],
                'stats' => [
                    'state' => 'xxx'
                ],
            ],
            [
                'nextUri' => 'http://example.com/2',
                'data' => [1, 2, 3],
                'stats' => [
                    'state' => 'xxx'
                ],
            ],
            [
                'stats' => [
                    'state' => 'xxx'
                ],
            ]
        ];
        $this->guzzler->queueResponse(
            new Response(200, [], json_encode($responses[0])),
            new Response(200, [], json_encode($responses[1])),
            new Response(200, [], json_encode($responses[2]))
        );

        $collector = mock(Collectorable::class);
        $collector->shouldReceive('collect')
            ->times(3);
        $collector->shouldReceive('get')
            ->once()
            ->andReturn(collect([1, 2, 3, 1, 2, 3]));


        $processor = new Processor($connection, $this->guzzler->getClient());
        $data = $processor->execute('Query String', $collector);

        $this->assertInstanceOf(Collection::class, $data);
        $this->assertSame([1, 2, 3, 1, 2, 3], $data->toArray());

        // Test http request
        $this->guzzler->assertHistoryCount(3);
        $this->guzzler->expects($this->once())
            ->post('http://presto.abc' . Processor::STATEMENT_URI)
            ->withHeader('X-Presto-User', 'test user')
            ->withHeader('X-Presto-Schema', 'test schema')
            ->withHeader('X-Presto-Catalog', 'test catalog')
            ->withBody('Query String');
        $this->guzzler->expects($this->once())
            ->get('http://example.com/1');
        $this->guzzler->expects($this->once())
            ->get('http://example.com/2');
    }

    protected function createConnection()
    {
        $mock = mock(Connection::class);
        $mock->shouldReceive('getHost')
            ->withNoArgs()
            ->andReturn('http://presto.abc')
            ->once();
        $mock->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn('test user')
            ->once();
        $mock->shouldReceive('getSchema')
            ->withNoArgs()
            ->andReturn('test schema')
            ->once();
        $mock->shouldReceive('getCatalog')
            ->withNoArgs()
            ->andReturn('test catalog')
            ->once();

        return $mock;
    }
}
