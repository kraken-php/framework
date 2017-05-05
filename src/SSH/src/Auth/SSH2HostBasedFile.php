<?php

namespace Kraken\SSH\Auth;

use Kraken\SSH\SSH2AuthInterface;

/**
 * Host based SSH2 authentication.
 */
class SSH2HostBasedFile implements SSH2AuthInterface
{
    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $hostname;

    /**
     * @var string
     */
    protected $publicKeyFile;

    /**
     * @var string
     */
    protected $privateKeyFile;

    /**
     * @var null|string
     */
    protected $passPhrase;

    /**
     * @var null|string
     */
    protected $localUsername;

    /**
     * @param string $username The authentication username
     * @param string $hostname The authentication hostname
     * @param string $publicKeyFile The path of the public key file
     * @param string $privateKeyFile The path of the private key file
     * @param string $passPhrase An optional pass phrase for the key
     * @param string $localUsername  An optional local usernale. If omitted, the username will be used
     */
    public function __construct($username, $hostname, $publicKeyFile, $privateKeyFile, $passPhrase = null, $localUsername = null)
    {
        $this->username = $username;
        $this->hostname = $hostname;
        $this->publicKeyFile = $publicKeyFile;
        $this->privateKeyFile = $privateKeyFile;
        $this->passPhrase = $passPhrase;
        $this->localUsername = $localUsername;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function authenticate($conn)
    {
        return @ssh2_auth_hostbased_file(
            $conn,
            $this->username,
            $this->hostname,
            $this->publicKeyFile,
            $this->privateKeyFile,
            $this->passPhrase,
            $this->localUsername
        );
    }
}
