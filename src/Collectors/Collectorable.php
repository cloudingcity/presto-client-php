<?php

declare(strict_types=1);

namespace Clouding\Presto\Collectors;

interface Collectorable
{
    /**
     * Collect needs data from presto response.
     *
     * @param object $response
     */
    public function collect(object $response);

    /**
     * Get collect data.
     *
     * @return array
     */
    public function get(): array;
}
