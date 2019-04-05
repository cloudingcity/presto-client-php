<?php

declare(strict_types=1);

namespace Clouding\Presto\Connection;

use Clouding\Presto\Container;
use Clouding\Presto\Exceptions\ConnectionNotFoundException;

class Manager
{
    /**
     * The manager's container.
     *
     * @var \Clouding\Presto\Container
     */
    protected $container;

    /**
     * The manager's connections.
     *
     * @var array
     */
    protected $connections;

    /**
     * Create a new manager instance.
     *
     * @param \Clouding\Presto\Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Get a connection.
     *
     * @param  string $name
     * @return \Clouding\Presto\Connection\Connection
     */
    public function connection(string $name): Connection
    {
        return $this->makeConnection($name);
    }

    /**
     * Make a connection.
     *
     * @param  string $name
     * @return \Clouding\Presto\Connection\Connection
     */
    protected function makeConnection(string $name): Connection
    {
        if (isset($this->connections[$name])) {
            return $this->connections[$name];
        }

        if (!isset($this->container[$name])) {
            throw new ConnectionNotFoundException("Connection not found: [$name]");
        }

        $this->connections[$name] = new Connection($this->container[$name]);

        return $this->connections[$name];
    }

    /**
     * Get all connections.
     *
     * @return array
     */
    public function getConnections(): array
    {
        return $this->container->toArray();
    }
}
