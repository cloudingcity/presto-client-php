<?php

declare(strict_types=1);

namespace Clouding\Presto\Tests\Connection;

use Clouding\Presto\Connection\Connection;
use Clouding\Presto\Connection\Manager;
use Clouding\Presto\Container;
use Clouding\Presto\Exceptions\ManagerException;
use PHPUnit\Framework\TestCase;

class ManagerTest extends TestCase
{
    public function testConnectionException()
    {
        $this->expectException(ManagerException::class);
        $this->expectExceptionMessage("Not found connection name of 'apple'");

        $container = new Container();
        $manager = new Manager($container);
        $manager->connection('apple');
    }

    public function testConnection()
    {
        $container = new Container(['super' => ['man'], 'ant' => ['man']]);
        $manager = new Manager($container);
        $super = $manager->connection('super');
        $super2 = $manager->connection('super');

        $this->assertInstanceOf(Connection::class, $super);
        $this->assertSame($super, $super2);
    }

    public function testGetConnections()
    {
        $connections = [
            'default' => [
                'fruit' => 'apple',
            ],
            'super' => [
                'fruit' => 'banana',
            ]
        ];
        $container = new Container($connections);
        $manager = new Manager($container);

        $this->assertSame($connections, $manager->getConnections());
    }
}
