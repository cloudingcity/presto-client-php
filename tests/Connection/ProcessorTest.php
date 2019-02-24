<?php

declare(strict_types=1);

namespace Clouding\Presto\Tests\Connection;

use Clouding\Presto\Connection\Connection;
use Clouding\Presto\Connection\Processor;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use function GuzzleHttp\Psr7\stream_for;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class ProcessorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testHandle()
    {
        $mockConnection = Mockery::mock(Connection::class);
        $mockConnection->shouldReceive('getHost')
            ->once();
        $mockConnection->shouldReceive('getUser')
            ->once();
        $mockConnection->shouldReceive('getSchema')
            ->once();
        $mockConnection->shouldReceive('getCatalog')
            ->once();

        $responseStubs = [
            [
                'nextUri' => 'http://example.com',
                'data' => [1, 2, 3],
                'stats' => [
                    'state' => 'xxx'
                ],
            ],
            [
                'data' => [1, 2, 3],
                'stats' => [
                    'state' => 'xxx'
                ],
            ]
        ];
        $mockHandler = new MockHandler([
            new Response(200, [], stream_for(json_encode($responseStubs[0]))),
            new Response(200, [], stream_for(json_encode($responseStubs[1]))),
        ]);
        $mockClient = new Client(['handler' => HandlerStack::create($mockHandler)]);

        $processor = new Processor($mockConnection, $mockClient);
        $data = $processor->handle('aaa');

        $this->assertSame([1, 2, 3, 1, 2, 3], $data->toArray());
    }
}
