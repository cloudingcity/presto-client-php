<?php

declare(strict_types=1);

namespace Clouding\Presto\Processor;

use Clouding\Presto\Exceptions\ResponseResolveException;
use Psr\Http\Message\ResponseInterface;

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
     * @var array
     */
    protected $collection = [];

    /**
     * Resolve response
     *
     * @param  \Psr\Http\Message\ResponseInterface  $response
     *
     * @throws \Clouding\Presto\Exceptions\ResponseResolveException
     */
    public function resolve(ResponseInterface $response)
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
     * @throws \Clouding\Presto\Exceptions\ResponseResolveException
     */
    protected function checkState(object $contents)
    {
        if ($contents->stats->state === self::FAILED) {
            $message = "{$contents->error->errorName}: {$contents->error->message}";
            throw new ResponseResolveException($message);
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
     * Collect data.
     *
     * @param object $contents
     */
    protected function collect(object $contents)
    {
        if (isset($contents->data)) {
            $this->collection = array_merge($this->collection, $contents->data);
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
     * Get collection.
     *
     * @return array
     */
    public function getCollection(): array
    {
        return $this->collection;
    }
}
