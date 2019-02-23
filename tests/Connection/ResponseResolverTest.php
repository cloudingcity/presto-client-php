<?php

declare(strict_types=1);

namespace Clouding\Presto\Tests\Connection;

use Clouding\Presto\Connection\ResponseResolver;
use Clouding\Presto\Exceptions\PrestoException;
use GuzzleHttp\Psr7\Response;
use function GuzzleHttp\Psr7\stream_for;
use PHPUnit\Framework\TestCase;
use Tightenco\Collect\Support\Collection;

class ResponseResolverTest extends TestCase
{
    public function testResolveException()
    {
        $stub = [
            'stats' => [
                'state' => ResponseResolver::FAILED
            ],
            'error' => [
                'message' => 'This is a error message',
                'errorName' => 'This is a error name',
            ]
        ];
        $mockResponse = new Response(200, [], stream_for(json_encode($stub)));

        $this->expectException(PrestoException::class);
        $this->expectExceptionMessage("{$stub['error']['errorName']}: {$stub['error']['message']}");

        $resolver = new ResponseResolver();
        $resolver->resolve($mockResponse);
    }

    public function testResolveWithNext()
    {
        $stub = [
            'nextUri' => 'http://example.com',
            'data' => [1, 2, 3],
            'stats' => [
                'state' => 'xxx'
            ],
        ];
        $mockResponse = new Response(200, [], stream_for(json_encode($stub)));

        $resolver = new ResponseResolver();

        $this->assertTrue($resolver->continue());
        $this->assertSame('', $resolver->getNextUri());

        $resolver->resolve($mockResponse);

        $this->assertTrue($resolver->continue());
        $this->assertSame($stub['nextUri'], $resolver->getNextUri());
        $this->assertInstanceOf(Collection::class, $resolver->getData());
        $this->assertSame($stub['data'], $resolver->getData()->toArray());
    }

    public function testResolveWithoutNext()
    {
        $stub = [
            'stats' => [
                'state' => 'xxx'
            ],
        ];
        $mockResponse = new Response(200, [], stream_for(json_encode($stub)));

        $resolver = new ResponseResolver();

        $this->assertTrue($resolver->continue());
        $this->assertSame('', $resolver->getNextUri());

        $resolver->resolve($mockResponse);

        $this->assertFalse($resolver->continue());
        $this->assertSame('', $resolver->getNextUri());
        $this->assertInstanceOf(Collection::class, $resolver->getData());
        $this->assertEmpty($resolver->getData()->toArray());
    }
}
