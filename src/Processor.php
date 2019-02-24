<?php

declare(strict_types=1);

namespace Clouding\Presto;

use Clouding\Presto\Connection\Connection;
use Clouding\Presto\Exceptions\ProcessorException;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Tightenco\Collect\Support\Collection;

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
     * The state of failed.
     *
     * @var string
     */
    const FAILED = 'FAILED';

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
     * Response next uri.
     *
     * @var ?string
     */
    protected $nextUri = '';

    /**
     * Check collect is associative.
     *
     * @var bool
     */
    protected $isCollectAssoc = false;

    /**
     * Collect response data.
     *
     * @var \Tightenco\Collect\Support\Collection
     */
    protected $collection;

    /**
     * Create a new instance.
     *
     * @param \Clouding\Presto\Connection\Connection  $connection
     * @param \GuzzleHttp\Client|null                 $client
     */
    public function __construct(Connection $connection, Client $client = null)
    {
        $this->connection = $connection;
        $this->client = $client ?? new Client();
        $this->collection = new Collection;
    }

    /**
     * Handle connection query.
     *
     * @param  string  $query
     * @return \Tightenco\Collect\Support\Collection
     *
     */
    public function execute(string $query): Collection
    {
        $this->resolve($this->sendQuery($query));

        while ($this->continue()) {
            usleep(static::SLEEP);

            $this->resolve($this->sendNext());
        }

        return $this->collection;
    }

    /**
     * Send query request.
     *
     * @param  string  $query
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function sendQuery(string $query): ResponseInterface
    {
        $baseUri = $this->connection->getHost() . static::STATEMENT_URI;
        $headers = [
            'X-Presto-User' => $this->connection->getUser(),
            'X-Presto-Schema' => $this->connection->getSchema(),
            'X-Presto-Catalog' => $this->connection->getCatalog(),
        ];

        return $this->client->post($baseUri, ['headers' => $headers, 'body' => $query]);
    }

    /**
     * Send next query.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function sendNext(): ResponseInterface
    {
        return $this->client->get($this->nextUri);
    }

    /**
     * Resolve response.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     */
    protected function resolve(ResponseInterface $response)
    {
        $contents = json_decode($response->getBody()->getContents());

        $this->checkState($contents);

        $this->setNextUri($contents);

        $this->collect($contents);
    }

    /**
     * Check response state.
     *
     * @param  object  $contents
     *
     * @throws \Clouding\Presto\Exceptions\ProcessorException
     */
    protected function checkState(object $contents)
    {
        if ($contents->stats->state === self::FAILED) {
            $message = "{$contents->error->errorName}: {$contents->error->message}";
            throw new ProcessorException($message);
        }
    }

    /**
     * Set next uri.
     *
     * @param  object  $contents
     */
    protected function setNextUri(object $contents)
    {
        $this->nextUri = $contents->nextUri ?? null;
    }

    /**
     * Set collect associative.
     */
    public function setCollectAssoc()
    {
        $this->isCollectAssoc = true;
    }

    /**
     * Collect data.
     *
     * @param object $contents
     */
    protected function collect(object $contents)
    {
        if (!isset($contents->data)) {
            return;
        }

        $data = $contents->data;

        if ($this->isCollectAssoc) {
            $columns = collect($contents->columns)->pluck('name');

            $data = collect($data)->map(function (array $row) use ($columns) {
                return $columns->combine($row)->toArray();
            });
        }

        $this->collection = $this->collection->merge($data);
    }

    /**
     * Determine if next uri is set or not.
     *
     * @return bool
     */
    protected function continue(): bool
    {
        return isset($this->nextUri);
    }
}
