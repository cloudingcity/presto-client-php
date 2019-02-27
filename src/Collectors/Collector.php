<?php

declare(strict_types=1);

namespace Clouding\Presto\Collectors;

use Clouding\Presto\Contracts\Collectorable;
use Tightenco\Collect\Support\Collection;

class Collector implements Collectorable
{
    /**
     * The collection of collect data.
     *
     * @var \Tightenco\Collect\Support\Collection
     */
    protected $collection;

    /**
     * Create a new collector instance.
     */
    public function __construct()
    {
        $this->collection = new Collection();
    }

    /**
     * Collect needs data from object.
     *
     * @param object $contents
     */
    public function collect(object $contents)
    {
        if (!isset($contents->data)) {
            return;
        }

        $this->collection = $this->collection->merge($contents->data);
    }

    /**
     * Get collect data.
     *
     * @return \Tightenco\Collect\Support\Collection
     */
    public function get(): Collection
    {
        return $this->collection;
    }
}
