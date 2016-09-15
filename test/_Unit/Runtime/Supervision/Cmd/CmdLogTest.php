<?php

namespace Kraken\_Unit\Runtime\Supervision;

use Kraken\_Unit\Runtime\_T\TSolver;
use Kraken\Log\Logger;
use Kraken\Runtime\Supervision\Cmd\CmdLog;
use Exception;
use StdClass;

class CmdLogTest extends TSolver
{
    /**
     * @var string
     */
    protected $class = CmdLog::class;

    /**
     *
     */
    public function testApisolver_InvokesProperAction()
    {
        $ex = new Exception();
        $params = [];

        $solver  = $this->createSolver();
        $logger  = $this->createLogger();
        $logger
            ->expects($this->once())
            ->method('log')
            ->with(Logger::EMERGENCY, $this->isType('string'));

        $this->callProtectedMethod(
            $solver, 'solver', [ $ex, $params ]
        );
    }
}
