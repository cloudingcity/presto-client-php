<?php

declare(strict_types=1);

namespace Clouding\Presto\Contracts;

use Tightenco\Collect\Support\Collection;

interface ProcessorInterface
{
    public function handle(string $statement): Collection;
}
