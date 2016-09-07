<?php

namespace Kraken\_Unit\Channel\Record;

use Kraken\Channel\Record\ResponseRecord;
use Kraken\Test\TUnit;

class ResponseRecordTest extends TUnit
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

        $this->assertSame('pid', $rep->getPid());
    }

    /**
     *
     */
    public function testApiAlias_ReturnsAlias()
    {
        $rep = $this->createResponse('pid', 'alias');

        $this->assertSame('alias', $rep->getAlias());
    }

    /**
     *
     */
    public function testApiTimeout_ReturnsTimeout()
    {
        $timeout = 5.0;
        $rep = $this->createResponse('pid', 'alias', $timeout);

        $this->assertSame($timeout, $rep->getTimeout());
    }

    /**
     *
     */
    public function testApiTimeoutIncrease_ReturnsTimeoutIncrease()
    {
        $timeoutIncrease = 5.0;
        $rep = $this->createResponse('pid', 'alias', 0.0, $timeoutIncrease);

        $this->assertSame($timeoutIncrease, $rep->getTimeoutIncrease());
    }

    /**
     * @param string $pid
     * @param string $alias
     * @param float $timeout
     * @param float $timeoutIncrease
     * @return ResponseRecord
     */
    public function createResponse($pid, $alias, $timeout = 0.0, $timeoutIncrease = 1.0)
    {
        return new ResponseRecord($pid, $alias, $timeout, $timeoutIncrease);
    }
}
