<?php

declare(strict_types=1);

namespace Clouding\Presto;

use Clouding\Presto\Contracts\ProcessorInterface;
use Tightenco\Collect\Support\Collection;

class QueryBuilder
{
    /**
     * The processor for query.
     *
     * @var \Clouding\Presto\Contracts\ProcessorInterface
     */
    protected $processor;

    /**
     * The raw of query.
     *
     * @var string
     */
    protected $raw = '';

    /**
     * Create a new query builder instance.
     *
     * @param \Clouding\Presto\Contracts\ProcessorInterface $processor
     */
    public function __construct(ProcessorInterface $processor)
    {
        $this->processor = $processor;
    }

    /**
     * Set raw query.
     *
     * @param  string  $query
     * @return \Clouding\Presto\QueryBuilder
     */
    public function raw(string $query): QueryBuilder
    {
        $this->raw = $query;

        return $this;
    }

    /**
     * Execute the query statement.
     *
     * @return \Tightenco\Collect\Support\Collection
     *
     * @throws \Clouding\Presto\Exceptions\ResponseResolveException
     */
    public function get(): Collection
    {
        return $this->processor->handle($this->toSql());
    }

    /**
     * Get raw query statement.
     *
     * @return string
     */
    public function toSql(): string
    {
        return $this->raw;
    }
}
