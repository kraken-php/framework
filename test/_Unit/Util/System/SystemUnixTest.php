<?php

namespace Kraken\_Unit\Util\System;

use Kraken\_Unit\Util\System\_Mock\SystemUnixMock;
use Kraken\Test\TUnit;
use Kraken\Util\System\SystemUnix;

class SystemUnixTest extends TUnit
{
    /**
     *
     */
    public function testApiRun_InvokesExecWithProperArguments()
    {
        $system = $this->createSystem();
        $system->run('myCommand');

        $this->assertSame([ 'myCommand >/dev/null 2>&1 & echo $!' ], $system->getArgs());
    }

    /**
     *
     */
    public function testApiKill_InvokesExecWithProperArguments()
    {
        $system = $this->createSystem();
        $system->kill('myCommand');

        $this->assertSame([ 'kill -9 myCommand >/dev/null 2>&1', null, null ], $system->getArgs());
    }

    /**
     *
     */
    public function testApiExistsPid_InvokesExecWithProperArguments()
    {
        $system = $this->createSystem();
        $system->existsPid('myCommand');

        $this->assertSame([ 'kill -0 myCommand >/dev/null 2>&1', null, null ], $system->getArgs());
    }

    /**
     * @return SystemUnix|SystemUnixMock
     */
    public function createSystem()
    {
        return new SystemUnixMock();
    }
}
