<?php

namespace Kraken\SSH\Auth;

use Kraken\SSH\SSH2AuthInterface;

/**
 * Username based SSH2 authentication.
 */
class SSH2None implements SSH2AuthInterface
{
    /**
     * @var string
     */
    protected $username;

    /**
     * @param string $username The authentication username
     */
    public function __construct($username)
    {
        $this->username = $username;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function authenticate($conn)
    {
        return true === @ssh2_auth_none($conn, $this->username);
    }
}
