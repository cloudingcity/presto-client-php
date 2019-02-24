<?php

declare(strict_types=1);

namespace Clouding\Presto;

use Clouding\Presto\Connection\Connection;
use Clouding\Presto\Connection\Manager;

class Presto
{
    /**
     * The connections.
     *
     * @var array
     */
    protected $connections = [];

    /**
     * Connection manager.
     *
     * @var \Clouding\Presto\Connection\Manager
     */
    protected $manager;

    /**
     * The current globally used instance.
     *
     * @var \Clouding\Presto\Presto
     */
    protected static $instance;

    /**
     * Register a connection.
     *
     * @param  array   $config
     * @param  string  $name
     * @return void
     */
    public function addConnection(array $config, $name = 'default')
    {
        $this->connections[$name] = $config;
    }

    /**
     * Make this instance available globally.
     */
    public function setAsGlobal()
    {
        $this->manager = new Manager($this->connections);

        static::$instance = $this;
    }

    /**
     * Get a fluent query builder instance.
     *
     * @param  string  $query
     * @param  string  $connection
     * @return \Clouding\Presto\QueryBuilder
     */
    public static function query($query, $connection = null): QueryBuilder
    {
        return static::$instance->connection($connection)->query($query);
    }

    /**
     * Get connection instance from manager.
     *
     * @param  string|null  $connection
     * @return \Clouding\Presto\Connection\Connection
     */
    public function connection(string $connection = null): Connection
    {
        $connection = $connection ?? 'default';

        return $this->manager->connection($connection);
    }

    /**
     * Get connections from manager.
     *
     * @return array
     */
    public static function getConnections(): array
    {
        return static::$instance->manager->getConnections();
    }
}
