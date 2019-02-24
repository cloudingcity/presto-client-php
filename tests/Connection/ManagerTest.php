<?php

declare(strict_types=1);

namespace Clouding\Presto\Tests\Connection;

use Clouding\Presto\Connection\Connection;
use Clouding\Presto\Connection\Manager;
use Clouding\Presto\Exceptions\ManagerException;
use PHPUnit\Framework\TestCase;

class ManagerTest extends TestCase
{
    public function testConnectionException()
    {
        $this->expectException(ManagerException::class);
        $this->expectExceptionMessage("Not found connection name of 'apple'");

        $manager = new Manager([]);
        $manager->connection('apple');
    }

    public function testConnection()
    {
        $manager = new Manager(
            [
                'default' => [
                    'fruit' => 'apple',
                ],
                'super' => [
                    'fruit' => 'banana',
                ]
            ]
        );
        $default = $manager->connection('default');
        $default2 = $manager->connection('default');

        $this->assertInstanceOf(Connection::class, $default);
        $this->assertSame($default, $default2);
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
        $manager = new Manager($connections);

        $this->assertSame($connections, $manager->getConnections());
    }
}
