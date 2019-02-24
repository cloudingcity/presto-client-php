<?php

declare(strict_types=1);

namespace Clouding\Presto\Tests;

use Clouding\Presto\Presto;
use Clouding\Presto\QueryBuilder;
use PHPUnit\Framework\TestCase;

class PrestoTest extends TestCase
{
    public function testSetAsGlobal()
    {
        $presto = new Presto();
        $presto->addConnection(['apple']);
        $presto->addConnection(['banana'], 'second');
        $presto->setAsGlobal();

        $connections = Presto::getConnections();

        $this->assertSame(['default' => ['apple'], 'second' => ['banana']], $connections);
    }

    public function testQuery()
    {
        $presto = new Presto();
        $presto->addConnection(['apple']);
        $presto->setAsGlobal();

        $this->assertInstanceOf(QueryBuilder::class, Presto::query('Hi there'));
    }
}
