<?php

namespace Kraken\_Unit\SSH\Auth;

use Kraken\SSH\Auth\SSH2Password;
use Kraken\SSH\SSH2AuthInterface;
use Kraken\Test\TUnit;

class SSH2PasswordTest extends TUnit
{
    /**
     *
     */
    public function testConstructor_CreatesProperInstance()
    {
        $user = 'user';
        $pass = 'pass';
        $auth = new SSH2Password($user, $pass);

        $this->assertInstanceOf(SSH2AuthInterface::class, $auth);
        $this->assertAttributeEquals($user, 'username', $auth);
        $this->assertAttributeEquals($pass, 'password', $auth);
    }

    /**
     *
     */
    public function testDestructor_DoesNotThrowException()
    {
        $user = 'user';
        $pass = 'pass';
        $auth = new SSH2Password($user, $pass);
        unset($auth);
    }
}
 