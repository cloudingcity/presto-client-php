<?php

declare(strict_types=1);

namespace Clouding\Presto\Connection;

use Clouding\Presto\Exceptions\ManagerException;

class Manager
{
    /**
     * Manage connections.
     *
     * @var array
     */
    protected $connections;

    /**
     * Connection instances.
     *
     * @var array
     */
    protected $instances;

    /**
     * Create a new manager instance.
     *
     * @param array $connections
     */
    public function __construct(array $connections)
    {
        $this->connections = $connections;
    }

    /**
     * Get a connection instance.
     *
     * @param  string $name
     * @return \Clouding\Presto\Connection\Connection
     */
    public function connection(string $name): Connection
    {
        return $this->makeConnection($name);
    }

    /**
     * Make a connection instance.
     *
     * @param  string  $name
     * @return \Clouding\Presto\Connection\Connection
     */
    protected function makeConnection(string $name): Connection
    {
        if (isset($this->instances[$name])) {
            return $this->instances[$name];
        }

        if (!isset($this->connections[$name])) {
            throw new ManagerException("Not found connection name of '$name'");
        }

        $this->instances[$name] = new Connection($this->connections[$name]);

        return $this->instances[$name];
    }

    /**
     * Get all connections.
     *
     * @return array
     */
    public function getConnections(): array
    {
        return $this->connections;
    }
}
