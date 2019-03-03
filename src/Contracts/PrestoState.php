<?php

declare(strict_types=1);

namespace Clouding\Presto\Contracts;

interface PrestoState
{
    /**
     * Query has been accepted and is awaiting execution.
     *
     * @var string
     */
    const QUEUED = 'QUEUED';

    /**
     * Query is being planned.
     *
     * @var string
     */
    const PLANNING = 'PLANNING';

    /**
     * Query execution is being started.
     *
     * @var string
     */
    const STARTING = 'STARTING';

    /**
     * Query has at least one running task.
     *
     * @var string
     */
    const RUNNING = 'RUNNING';

    /**
     * Query is blocked and is waiting for resources (buffer space, memory, splits, etc.).
     *
     * @var string
     */
    const BLOCKED = 'BLOCKED';

    /**
     * Query is finishing (e.g. commit for autocommit queries).
     *
     * @var string
     */
    const FINISHING = 'FINISHING';

    /**
     * Query has finished executing and all output has been consumed.
     *
     * @var string
     */
    const FINISHED = 'FINISHED';


    /**
     * Query execution failed.
     *
     * @var string
     */
    const FAILED = 'FAILED';
}
