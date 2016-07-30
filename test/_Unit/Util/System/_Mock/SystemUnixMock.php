<?php

namespace Kraken\_Unit\Util\System\_Mock;

use Kraken\Util\System\SystemUnix;

class SystemUnixMock extends SystemUnix
{
    /**
     * @var string
     */
    protected $executor;

    /**
     * @var mixed[]
     */
    protected $args;

    /**
     *
     */
    public function __construct()
    {
        $this->executor = [ $this, 'exec' ];
        $this->args = [];
    }

    /**
     *
     */
    public function exec()
    {
        $this->args = func_get_args();
    }

    /**
     * @return mixed[]
     */
    public function getArgs()
    {
        return $this->args;
    }
}
