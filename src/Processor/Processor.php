<?php

declare(strict_types=1);

namespace Clouding\Presto\Processor;

use Clouding\Presto\Connection\Connection;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

class Processor
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
     * @return array
     *
     * @throws \Clouding\Presto\Exceptions\ResponseResolveException
     */
    public function handle(string $statement): array
    {
        $resolver = new ResponseResolver();

        $resolver->resolve($this->sendStatement($statement));

        while ($resolver->continue()) {
            usleep(static::SLEEP);

            $resolver->resolve(
                $this->client->get($resolver->getNextUri())
            );
        }

        return $resolver->getCollection();
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
