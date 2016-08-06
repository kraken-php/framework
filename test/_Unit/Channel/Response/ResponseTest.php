<?php

namespace Kraken\_Unit\Channel\Request;

use Kraken\Channel\Response\Response;
use Kraken\Test\TUnit;

class ResponseTest extends TUnit
{
    /**
     *
     */
    public function testApiConstructor_DoesNotThrowException()
    {
        $this->createResponse('pid', 'alias');
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $rep = $this->createResponse('pid', 'alias');
        unset($rep);
    }

    /**
     *
     */
    public function testApiPid_ReturnsPid()
    {
        $rep = $this->createResponse('pid', 'alias');

        $this->assertSame('pid', $rep->pid());
    }

    /**
     *
     */
    public function testApiAlias_ReturnsAlias()
    {
        $rep = $this->createResponse('pid', 'alias');

        $this->assertSame('alias', $rep->alias());
    }

    /**
     *
     */
    public function testApiTimeout_ReturnsTimeout()
    {
        $timeout = 5.0;
        $rep = $this->createResponse('pid', 'alias', $timeout);

        $this->assertSame($timeout, $rep->timeout());
    }

    /**
     *
     */
    public function testApiTimeoutIncrease_ReturnsTimeoutIncrease()
    {
        $timeoutIncrease = 5.0;
        $rep = $this->createResponse('pid', 'alias', 0.0, $timeoutIncrease);

        $this->assertSame($timeoutIncrease, $rep->timeoutIncrease());
    }

    /**
     * @param string $pid
     * @param string $alias
     * @param float $timeout
     * @param float $timeoutIncrease
     * @return Response
     */
    public function createResponse($pid, $alias, $timeout = 0.0, $timeoutIncrease = 1.0)
    {
        return new Response($pid, $alias, $timeout, $timeoutIncrease);
    }
}
