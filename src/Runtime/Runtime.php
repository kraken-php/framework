<?php

namespace Kraken\Runtime;

abstract class Runtime
{
    /**
     * @var string
     */
    const UNIT_UNDEFINED = 'Undefined';

    /**
     * @var string
     */
    const UNIT_PROCESS = 'Process';

    /**
     * @var string
     */
    const UNIT_THREAD = 'Thread';

    /**
     * @var string
     */
    const PARENT_UNDEFINED = 'undefined';

    /**
     * @var string
     */
    const RESERVED_CONSOLE_CLIENT = 'Client';

    /**
     * @var string
     */
    const RESERVED_CONSOLE_SERVER = 'Server';

    /**
     * @var int
     */
    const CREATE_DEFAULT = 0;

    /**
     * @var int
     */
    const CREATE_FORCE_SOFT = 1;

    /**
     * @var int
     */
    const CREATE_FORCE_HARD = 2;

    /**
     * @var int
     */
    const CREATE_FORCE = 3;

    /**
     * @var int
     */
    const DESTROY_KEEP = 0;

    /**
     * @var int
     */
    const DESTROY_FORCE_SOFT = 1;

    /**
     * @var int
     */
    const DESTROY_FORCE_HARD = 2;

    /**
     * @var int
     */
    const DESTROY_FORCE = 3;

    /**
     * @var int
     */
    const STATE_CREATED = 1;

    /**
     * @var int
     */
    const STATE_DESTROYED = 2;

    /**
     * @var int
     */
    const STATE_STARTED = 4;

    /**
     * @var int
     */
    const STATE_STOPPED = 8;
}
