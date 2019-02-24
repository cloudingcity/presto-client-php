<?php

declare(strict_types=1);

namespace Clouding\Presto\Connection;

use Clouding\Presto\Contracts\ProcessorInterface;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Tightenco\Collect\Support\Collection;

class Processor implements ProcessorInterface
{
    /**
     * The statement uri.
     *
     * @var string
     */
    const STATEMENT_URI = '/v1/statement';

    /**
     * Resend request sleep microseconds.
     *
     * @var int
     */
    const SLEEP = 50000;

    /**
     * The connection information.
     *
     * @var \Clouding\Presto\Connection\Connection
     */
    protected $connection;

    /**
     * Http client.
     *
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * Create a new instance.
     *
     * @param \Clouding\Presto\Connection\Connection  $connection
     * @param \GuzzleHttp\Client|null                 $client
     */
    public function __construct(Connection $connection, Client $client = null)
    {
        $this->connection = $connection;
        $this->setupHttpClient($client ?? new Client());
    }

    /**
     * Setup http client.
     *
     * @param \GuzzleHttp\Client $client
     */
    protected function setupHttpClient(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Handle connection query.
     *
     * @param  string  $statement
     * @return \Tightenco\Collect\Support\Collection
     *
     * @throws \Clouding\Presto\Exceptions\PrestoException
     */
    public function handle(string $statement): Collection
    {
        $resolver = new ResponseResolver();

        $resolver->resolve($this->sendStatement($statement));

        while ($resolver->continue()) {
            usleep(static::SLEEP);

            $resolver->resolve(
                $this->client->get($resolver->getNextUri())
            );
        }

        return $resolver->getData();
    }

    /**
     * Send statement request.
     *
     * @param  string  $body
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function sendStatement(string $body): ResponseInterface
    {
        $baseUri = $this->connection->getHost() . static::STATEMENT_URI;
        $headers = [
            'X-Presto-User' => $this->connection->getUser(),
            'X-Presto-Schema' => $this->connection->getSchema(),
            'X-Presto-Catalog' => $this->connection->getCatalog(),
        ];

        return $this->client->post($baseUri, compact('headers', 'body'));
    }
}
