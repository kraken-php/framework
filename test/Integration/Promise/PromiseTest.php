<?php

namespace Kraken\Test\Integration\Promise;

use Kraken\Promise\Deferred;
use Kraken\Test\Integration\TestCase;

class PromiseTest extends TestCase
{
    /**
     *
     */
    public function testPromise_SupportsVeryDeepNesting()
    {
        $deferreds = [];

        for ($i = 0; $i < 50; $i++)
        {
            $deferreds[] = $d = new Deferred();
            $p = $d->promise();
            $last = $p;
            for ($j = 0; $j < 500; $j++)
            {
                $last = $last->then(function($result) {
                    return $result;
                });
            }
        }

        $p = null;

        foreach ($deferreds as $d)
        {
            if ($p) {
                $d->resolve($p);
            }
            $p = $d->promise();
        }

        $deferreds[0]->resolve(true);

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo(true));

        $deferreds[0]->promise()->then($mock);
    }
}
