<?php

namespace Kraken\_Unit\Channel\Record;

use Kraken\_Unit\Channel\_Mock\Record\RequestRecordStorage;
use Kraken\Channel\Record\RequestRecord;
use Kraken\Util\Support\TimeSupport;
use Kraken\Test\TUnit;
use Exception;

class RequestRecordStorageTest extends TUnit
{
    /**
     *
     */
    public function testApiCreateRequest_CreatesRequest()
    {
        $obj = $this->createRequestRecordStorage();

        $pid = 'pid';
        $success = function() {};
        $failure = function() {};
        $abort = function() {};
        $timeout = 0.0;

        $req = $this->callProtectedMethod($obj, 'createRequest', [ $pid, $success, $failure, $abort, $timeout ]);

        $this->assertSame($pid, $req->getPid());
        $this->assertSame($success, $req->onSuccess());
        $this->assertSame($failure, $req->onFailure());
        $this->assertSame($abort, $req->onCancel());
        $this->assertSame($timeout, $req->getTimeout());
    }

    /**
     *
     */
    public function testApiCreateRequest_AddsNowToPositiveTimeout()
    {
        $obj = $this->createRequestRecordStorage();

        $pid = 'pid';
        $timeout = 1.0;

        $req = $this->callProtectedMethod($obj, 'createRequest', [ $pid, null, null, null, $timeout ]);

        $timeout = $timeout * 1000 + TimeSupport::now();;

        $this->assertGreaterThan($timeout - 1000, $req->getTimeout());
        $this->assertLessThanOrEqual($timeout, $req->getTimeout());
    }

    /**
     *
     */
    public function testApiExistsRequest_ReturnsFalse_WhenRequestDoesNotExist()
    {
        $obj = $this->createRequestRecordStorage();
        $pid = 'pid';

        $exists = $this->callProtectedMethod($obj, 'existsRequest', [ $pid ]);

        $this->assertFalse($exists);
    }

    /**
     *
     */
    public function testApiExistsRequest_ReturnsTrue_WhenRequestDoesExist()
    {
        $obj = $this->createRequestRecordStorage();
        $pid = 'pid';

        $this->setProtectedProperty($obj, 'reqs', [ $pid => new RequestRecord($pid) ]);

        $exists = $this->callProtectedMethod($obj, 'existsRequest', [ $pid ]);

        $this->assertTrue($exists);
    }

    /**
     *
     */
    public function testApiAddRequest_AddsRequest()
    {
        $obj = $this->createRequestRecordStorage();
        $pid = 'pid';

        $this->assertFalse($this->callProtectedMethod($obj, 'existsRequest', [ $pid ]));
        $this->callProtectedMethod($obj, 'addRequest', [ $pid, new RequestRecord($pid) ]);
        $this->assertTrue($this->callProtectedMethod($obj, 'existsRequest', [ $pid ]));
    }

    /**
     *
     */
    public function testApiGetRequest_ReturnsRequest()
    {
        $obj = $this->createRequestRecordStorage();
        $pid = 'pid';
        $req = new RequestRecord($pid);

        $this->callProtectedMethod($obj, 'addRequest', [ $pid, $req ]);
        $result = $this->callProtectedMethod($obj, 'getRequest', [ $pid ]);

        $this->assertSame($result, $req);
    }

    /**
     *
     */
    public function testApiResolveRequest_ResolvesRequest()
    {
        $obj = $this->createRequestRecordStorage();
        $pid = 'pid';
        $val = 'message';

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($val);

        $req = new RequestRecord($pid, $callable);

        $this->callProtectedMethod($obj, 'addRequest', [ $pid, $req ]);
        $this->callProtectedMethod($obj, 'resolveRequest', [ $pid, $val ]);
    }

    /**
     *
     */
    public function testApiRejectRequest_RejectsRequest()
    {
        $obj = $this->createRequestRecordStorage();
        $pid = 'pid';
        $exp = new Exception('message');

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($exp);

        $req = new RequestRecord($pid, null, $callable);

        $this->callProtectedMethod($obj, 'addRequest', [ $pid, $req ]);
        $this->callProtectedMethod($obj, 'rejectRequest', [ $pid, $exp ]);
    }

    /**
     *
     */
    public function testApiCancelRequest_CancelsRequest()
    {
        $obj = $this->createRequestRecordStorage();
        $pid = 'pid';
        $exp = new Exception('message');

        $callable = $this->createCallableMock();
        $callable
            ->expects($this->once())
            ->method('__invoke')
            ->with($exp);

        $req = new RequestRecord($pid, null, null, $callable);

        $this->callProtectedMethod($obj, 'addRequest', [ $pid, $req ]);
        $this->callProtectedMethod($obj, 'cancelRequest', [ $pid, $exp ]);
    }

    /**
     * @return RequestRecordStorage
     */
    public function createRequestRecordStorage()
    {
        return new RequestRecordStorage();
    }
}
