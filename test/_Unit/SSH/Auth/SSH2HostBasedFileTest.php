<?php

namespace Kraken\_Unit\SSH\Auth;

use Kraken\SSH\Auth\SSH2HostBasedFile;
use Kraken\Test\TUnit;

class SSH2HostBasedFileTest extends TUnit
{
    /**
     *
     */
    public function testConstructor_CreatesProperInstance()
    {
        $user = 'user';
        $host = 'example.com';
        $publicKey = 'path/public.key';
        $privateKey = 'path/private.key';
        $passPhrase = 'passPhrase';
        $localUser = 'localUser';

        $auth = new SSH2HostBasedFile($user, $host, $publicKey, $privateKey, $passPhrase, $localUser);

        $this->assertInstanceOf(SSH2HostBasedFile::class, $auth);
        $this->assertAttributeEquals($user, 'username', $auth);
        $this->assertAttributeEquals($host, 'hostname', $auth);
        $this->assertAttributeEquals($publicKey, 'publicKeyFile', $auth);
        $this->assertAttributeEquals($privateKey, 'privateKeyFile', $auth);
        $this->assertAttributeEquals($passPhrase, 'passPhrase', $auth);
        $this->assertAttributeEquals($localUser, 'localUsername', $auth);
    }

    /**
     *
     */
    public function testDestructor_DoesNotThrowThrowable()
    {
        $user = 'user';
        $host = 'example.com';
        $publicKey = 'path/public.key';
        $privateKey = 'path/private.key';
        $passPhrase = 'passPhrase';
        $localUser = 'localUser';

        $auth = new SSH2HostBasedFile($user, $host, $publicKey, $privateKey, $passPhrase, $localUser);
        unset($auth);
    }
}
 