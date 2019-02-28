<?php

declare(strict_types=1);

namespace Clouding\Presto\Tests;

use Clouding\Presto\Connection\Connection;
use Clouding\Presto\Contracts\Collectorable;
use Clouding\Presto\Exceptions\ProcessorException;
use Clouding\Presto\Processor;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use function GuzzleHttp\Psr7\stream_for;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Tightenco\Collect\Support\Collection;

class ProcessorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testExecuteException()
    {
        $connection = $this->getConnection();
        $response = [
            'stats' => [
                'state' => Processor::FAILED
            ],
            'error' => [
                'message' => 'This is a error message',
                'errorName' => 'This is a error name',
            ]
        ];
        $handler = new MockHandler([
            new Response(200, [], stream_for(json_encode($response))),
        ]);
        $client = new Client(['handler' => HandlerStack::create($handler)]);
        $collector = mock(Collectorable::class);


        $this->expectException(ProcessorException::class);
        $this->expectExceptionMessage("{$response['error']['errorName']}: {$response['error']['message']}");

        $processor = new Processor($connection, $client);
        $processor->execute('Go to school', $collector);
    }

    public function testExecute()
    {
        $connection = $this->getConnection();

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
        $handler = new MockHandler([
            new Response(200, [], stream_for(json_encode($responses[0]))),
            new Response(200, [], stream_for(json_encode($responses[1]))),
            new Response(200, [], stream_for(json_encode($responses[2]))),
        ]);
        $client = new Client(['handler' => HandlerStack::create($handler)]);

        $collector = mock(Collectorable::class);
        $collector->shouldReceive('collect')
            ->times(3);
        $collector->shouldReceive('get')
            ->once()
            ->andReturn(collect([1, 2, 3, 1, 2, 3]));


        $processor = new Processor($connection, $client);
        $data = $processor->execute('aaa', $collector);

        $this->assertInstanceOf(Collection::class, $data);
        $this->assertSame([1, 2, 3, 1, 2, 3], $data->toArray());
    }

    protected function getConnection()
    {
        $mock = mock(Connection::class);
        $mock->shouldReceive('getHost')
            ->once();
        $mock->shouldReceive('getUser')
            ->once();
        $mock->shouldReceive('getSchema')
            ->once();
        $mock->shouldReceive('getCatalog')
            ->once();

        return $mock;
    }
}
