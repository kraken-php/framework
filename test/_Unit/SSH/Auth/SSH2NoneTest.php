<?php

namespace Kraken\_Unit\SSH\Auth;

use Kraken\SSH\Auth\SSH2None;
use Kraken\SSH\SSH2AuthInterface;
use Kraken\Test\TUnit;

class SSH2NoneTest extends TUnit
{
    /**
     *
     */
    public function testConstructor_CreatesProperInstance()
    {
        $user = 'user';
        $auth = new SSH2None($user);

        $this->assertInstanceOf(SSH2AuthInterface::class, $auth);
        $this->assertAttributeEquals($user, 'username', $auth);
    }

    /**
     *
     */
    public function testDestructor_DoesNotThrowException()
    {
        $user = 'user';
        $auth = new SSH2None($user);
        unset($auth);
    }
}
 