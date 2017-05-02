<?php

namespace Kraken\SSH\Auth;

use Kraken\SSH\SSH2AuthInterface;

/**
 * Agent based SSH2 authentication
 */
class SSH2Agent implements SSH2AuthInterface
{
    /**
     * @var string
     */
    protected $username;

    /**
     * Constructor
     *
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
        return @ssh2_auth_agent(
            $conn,
            $this->username
        );
    }
}
