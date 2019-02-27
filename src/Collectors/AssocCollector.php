<?php

declare(strict_types=1);

namespace Clouding\Presto\Collectors;

use Clouding\Presto\Contracts\Collectorable;
use Tightenco\Collect\Support\Collection;

class AssocCollector implements Collectorable
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
     * Collect needs data from object
     *
     * @param object $object
     */
    public function collect(object $object)
    {
        if (!isset($object->data, $object->columns)) {
            return;
        }

        $columns = (new Collection($object->columns))->pluck('name');
        $data = (new Collection($object->data))->map(function (array $row) use ($columns) {
            return $columns->combine($row)->toArray();
        });

        $this->collection = $this->collection->merge($data);
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
