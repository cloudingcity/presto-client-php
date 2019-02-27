<?php

declare(strict_types=1);

namespace Clouding\Presto\Contracts;

interface Collectorable
{
    /**
     * Collect needs data from object.
     *
     * @param object $object
     */
    public function collect(object $object);

    /**
     * Get collect data.
     *
     * @return \Tightenco\Collect\Support\Collection
     */
    public function get();
}
