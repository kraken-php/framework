<?php

namespace Kraken\SSH\Auth;

use Kraken\SSH\SSH2AuthInterface;

/**
 * Password based SSH2 authentication
 */
class SSH2Password implements SSH2AuthInterface
{
    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * @param string $username The authentication username
     * @param string $password The authentication password
     */
    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function authenticate($conn)
    {
        return @ssh2_auth_password($conn, $this->username, $this->password);
    }
}
