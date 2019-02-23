<?php

declare(strict_types=1);

namespace Clouding\Presto\Connection;

use Clouding\Presto\Exceptions\PrestoException;
use Psr\Http\Message\ResponseInterface;
use Tightenco\Collect\Support\Collection;

class ResponseResolver
{
    /**
     * The state of failed.
     *
     * @var string
     */
    const FAILED = 'FAILED';

    /**
     * Response next uri.
     *
     * @var ?string
     */
    protected $nextUri = '';

    /**
     * Collect response data.
     *
     * @var \Tightenco\Collect\Support\Collection
     */
    protected $data;

    /**
     * Create a new instance.
     */
    public function __construct()
    {
        $this->data = new Collection();
    }

    /**
     * Resolve response
     *
     * @param  \Psr\Http\Message\ResponseInterface  $response
     *
     * @throws \Clouding\Presto\Exceptions\PrestoException
     */
    public function resolve(ResponseInterface $response)
    {
        $contents = json_decode($response->getBody()->getContents());

        $this->checkState($contents);

        $this->setNextUri($contents);

        $this->setData($contents);
    }

    /**
     * Check response state.
     *
     * @param  object  $contents
     *
     * @throws \Clouding\Presto\Exceptions\PrestoException
     */
    protected function checkState(object $contents)
    {
        if ($contents->stats->state === self::FAILED) {
            $message = "{$contents->error->errorName}: {$contents->error->message}";
            throw new PrestoException($message);
        }
    }

    /**
     * Set next uri.
     *
     * @param  object  $response
     */
    protected function setNextUri(object $response)
    {
        $this->nextUri = $response->nextUri ?? null;
    }

    /**
     * Set response data.
     *
     * @param object $contents
     */
    protected function setData(object $contents)
    {
        if (isset($contents->data)) {
            $this->data = $this->data->merge($contents->data);
        }
    }

    /**
     * Determine if next uri is set or not.
     *
     * @return bool
     */
    public function continue(): bool
    {
        return isset($this->nextUri);
    }

    /**
     * Get next uri.
     *
     * @return string
     */
    public function getNextUri(): string
    {
        return $this->nextUri ?? '';
    }

    /**
     * Get data.
     *
     * @return \Tightenco\Collect\Support\Collection
     */
    public function getData(): Collection
    {
        return $this->data;
    }
}
