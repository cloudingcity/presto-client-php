<?php

declare(strict_types=1);

namespace Clouding\Presto\Tests\Connection;

use Clouding\Presto\Connection\Connection;
use Clouding\Presto\QueryBuilder;
use PHPUnit\Framework\TestCase;

class ConnectionTest extends TestCase
{
    public function testDefaultConfig()
    {
        $connection = new Connection();

        $this->assertSame(Connection::DEFAULT_USER, $connection->getUser());
        $this->assertSame(Connection::DEFAULT_HOST, $connection->getHost());
        $this->assertSame('', $connection->getSchema());
        $this->assertSame(Connection::DEFAULT_CATALOG, $connection->getCatalog());
    }

    public function testConfig()
    {
        $config = [
            'user' => 'shawn',
            'host' => '1.1.1.1',
            'schema' => 'default',
            'catalog' => 'default',
        ];

        $connection = new Connection($config);

        $this->assertSame($config['user'], $connection->getUser());
        $this->assertSame($config['host'], $connection->getHost());
        $this->assertSame($config['schema'], $connection->getSchema());
        $this->assertSame($config['catalog'], $connection->getCatalog());
    }

    public function testQuery()
    {
        $connection = new Connection();
        $builder = $connection->query('I am handsome');

        $this->assertInstanceOf(QueryBuilder::class, $builder);
    }
}
