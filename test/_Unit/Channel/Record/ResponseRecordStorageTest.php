<?php

namespace Kraken\_Unit\Channel\Record;

use Kraken\_Unit\Channel\_Mock\Record\ResponseRecordStorage;
use Kraken\Channel\Record\ResponseRecord;
use Kraken\Util\Support\TimeSupport;
use Kraken\Test\TUnit;
use Exception;

class ResponseRecordStorageTest extends TUnit
{
    /**
     *
     */
    public function testApiCreateResponse_CreatesResponse()
    {
        $obj = $this->createResponseRecordStorage();

        $pid = 'pid';
        $alias = 'alias';
        $timeout = 2.0;
        $timeoutIncrease = 3.0;

        $rep = $this->callProtectedMethod($obj, 'createResponse', [ $pid, $alias, $timeout, $timeoutIncrease ]);

        $this->assertSame($pid, $rep->getPid());
        $this->assertSame($alias, $rep->getAlias());
        $this->assertSame($timeout, $rep->getTimeout());
        $this->assertSame($timeoutIncrease, $rep->getTimeoutIncrease());
    }

    /**
     *
     */
    public function testApiExistsResponse_ReturnsFalse_WhenResponseDoesNotExist()
    {
        $obj = $this->createResponseRecordStorage();
        $pid = 'pid';

        $exists = $this->callProtectedMethod($obj, 'existsResponse', [ $pid ]);

        $this->assertFalse($exists);
    }

    /**
     *
     */
    public function testApiExistsResponse_ReturnsTrue_WhenResponseDoesExist()
    {
        $obj = $this->createResponseRecordStorage();
        $pid = 'pid';
        $alias = 'alias';

        $this->setProtectedProperty($obj, 'reps', [ $pid => new ResponseRecord($pid, $alias) ]);

        $exists = $this->callProtectedMethod($obj, 'existsResponse', [ $pid ]);

        $this->assertTrue($exists);
    }

    /**
     *
     */
    public function testApiAddResponse_AddsResponse()
    {
        $obj = $this->createResponseRecordStorage();
        $pid = 'pid';
        $alias = 'alias';

        $this->assertFalse($this->callProtectedMethod($obj, 'existsResponse', [ $pid ]));
        $this->callProtectedMethod($obj, 'addResponse', [ $pid, new ResponseRecord($pid, $alias) ]);
        $this->assertTrue($this->callProtectedMethod($obj, 'existsResponse', [ $pid ]));
    }

    /**
     *
     */
    public function testApiResolveOrReject_ExpiresResponse()
    {
        $obj = $this->createResponseRecordStorage();
        $pid = 'pid';
        $alias = 'alias';
        $rep = new ResponseRecord($pid, $alias);
        $ex = new Exception();

        $this->callProtectedMethod($obj, 'addResponse', [ $pid, $rep ]);
        $this->assertSame([ 'pid' => $rep ], $this->getProtectedProperty($obj, 'reps'));
        $this->assertFalse(array_key_exists('pid', $this->getProtectedProperty($obj, 'handledReps')));

        $this->callProtectedMethod($obj, 'resolveOrRejectResponse', [ $pid, $ex ]);
        $this->assertSame([], $this->getProtectedProperty($obj, 'reps'));
        $this->assertTrue(array_key_exists('pid', $this->getProtectedProperty($obj, 'handledReps')));
    }

    /**
     *
     */
    public function testApiGetResponse_ReturnsResponse()
    {
        $obj = $this->createResponseRecordStorage();
        $pid = 'pid';
        $alias = 'alias';
        $rep = new ResponseRecord($pid, $alias);

        $this->callProtectedMethod($obj, 'addResponse', [ $pid, $rep ]);
        $result = $this->callProtectedMethod($obj, 'getResponse', [ $pid ]);

        $this->assertSame($result, $rep);
    }

    /**
     *
     */
    public function testApiUnfinishedResponses_ReturnsResponsesWhichTimeoutIsLessThanTimeNow()
    {
        $obj = $this->createResponseRecordStorage();
        $now = TimeSupport::now();
        $rep1 = new ResponseRecord($pid1 = 'pid1',  $alias1 = 'alias1', $now + 1e6);
        $rep2 = new ResponseRecord($pid2 = 'pid2',  $alias2 = 'alias2', $now);

        $this->callProtectedMethod($obj, 'addResponse', [ $pid1, $rep1 ]);
        $this->callProtectedMethod($obj, 'addResponse', [ $pid2, $rep2 ]);

        $this->assertSame([ $rep2 ], $this->callProtectedMethod($obj, 'unfinishedResponses'));
    }

    /**
     *
     */
    public function testApiExpireResponses_ExpiresResponsesWhichTimeoutIsLessThanTimeNow()
    {
        $obj = $this->createResponseRecordStorage();
        $now = TimeSupport::now();
        $rep1 = new ResponseRecord($pid1 = 'pid1',  $alias1 = 'alias1', $now + 1e6);
        $rep2 = new ResponseRecord($pid2 = 'pid2',  $alias2 = 'alias2', $now);

        $this->callProtectedMethod($obj, 'addResponse', [ $pid1, $rep1 ]);
        $this->callProtectedMethod($obj, 'addResponse', [ $pid2, $rep2 ]);

        $this->callProtectedMethod($obj, 'resolveOrRejectResponse', [ $pid2, new Exception ]);
        $this->assertTrue(array_key_exists($pid2, $this->getProtectedProperty($obj, 'handledReps')));

        $reps = $this->getProtectedProperty($obj, 'handledReps');
        $this->setProtectedProperty($reps[$pid2], 'timeout', $now - 1000);

        $this->callProtectedMethod($obj, 'expireResponses');
        $this->assertFalse(array_key_exists('pid', $this->getProtectedProperty($obj, 'handledReps')));
    }

    /**
     * @return ResponseRecordStorage
     */
    public function createResponseRecordStorage()
    {
        return new ResponseRecordStorage();
    }
}
