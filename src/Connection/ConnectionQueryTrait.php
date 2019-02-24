<?php
declare(strict_types=1);

namespace Clouding\Presto\Connection;

use Clouding\Presto\Contracts\ProcessorInterface;
use Clouding\Presto\QueryBuilder;

trait ConnectionQueryTrait
{
    /**
     * Begin a fluent query against a raw query.
     *
     * @param  string  $query
     * @return \Clouding\Presto\QueryBuilder
     */
    public function query(string $query): QueryBuilder
    {
        return $this->getBuilder()->raw($query);
    }

    /**
     * Get query builder with processor.
     *
     * @param  \Clouding\Presto\Contracts\ProcessorInterface|null  $processor
     * @return \Clouding\Presto\QueryBuilder
     */
    protected function getBuilder(ProcessorInterface $processor = null): QueryBuilder
    {
        $processor = $processor ?? new Processor($this);

        return new QueryBuilder($processor);
    }
}
