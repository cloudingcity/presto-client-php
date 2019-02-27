<?php

declare(strict_types=1);

namespace Clouding\Presto\Tests;

use Clouding\Presto\Connection\Connection;
use Clouding\Presto\Exceptions\ProcessorException;
use Clouding\Presto\Processor;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use function GuzzleHttp\Psr7\stream_for;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Tightenco\Collect\Support\Collection;

class ProcessorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testExecuteException()
    {
        $mockConnection = $this->getMockConnection();

        $responseStub = [
            'stats' => [
                'state' => Processor::FAILED
            ],
            'error' => [
                'message' => 'This is a error message',
                'errorName' => 'This is a error name',
            ]
        ];
        $mockHandler = new MockHandler([
            new Response(200, [], stream_for(json_encode($responseStub))),
        ]);
        $mockClient = new Client(['handler' => HandlerStack::create($mockHandler)]);

        $this->expectException(ProcessorException::class);
        $this->expectExceptionMessage("{$responseStub['error']['errorName']}: {$responseStub['error']['message']}");

        $processor = new Processor($mockConnection, $mockClient);
        $processor->execute('Go to school');
    }

    public function testExecute()
    {
        $mockConnection = $this->getMockConnection();

        $responseStubs = [
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
        $mockHandler = new MockHandler([
            new Response(200, [], stream_for(json_encode($responseStubs[0]))),
            new Response(200, [], stream_for(json_encode($responseStubs[1]))),
            new Response(200, [], stream_for(json_encode($responseStubs[2]))),
        ]);
        $mockClient = new Client(['handler' => HandlerStack::create($mockHandler)]);

        $processor = new Processor($mockConnection, $mockClient);
        $data = $processor->execute('aaa');

        $this->assertInstanceOf(Collection::class, $data);
        $this->assertSame([1, 2, 3, 1, 2, 3], $data->toArray());
    }

    public function testExecuteAssoc()
    {
        $mockConnection = $this->getMockConnection();

        $responseStubs = [
            [
                'columns' => [['name' => 'title'], ['name' => 'body']],
                'data' => [['Title 1', 'Body 1'], ['Title 2', 'Body 2']],
                'stats' => [
                    'state' => 'xxx'
                ],
            ]
        ];
        $mockHandler = new MockHandler([
            new Response(200, [], stream_for(json_encode($responseStubs[0]))),
        ]);
        $mockClient = new Client(['handler' => HandlerStack::create($mockHandler)]);

        $processor = new Processor($mockConnection, $mockClient);
        $processor->setCollectAssoc();
        $data = $processor->execute('aaa');

        $this->assertInstanceOf(Collection::class, $data);

        $expected = [['title' => 'Title 1', 'body' => 'Body 1'], ['title' => 'Title 2', 'body' => 'Body 2']];
        $this->assertSame($expected, $data->toArray());
    }

    protected function getMockConnection()
    {
        $mock = Mockery::mock(Connection::class);
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
