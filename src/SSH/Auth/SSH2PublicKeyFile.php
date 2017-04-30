<?php

namespace Kraken\SSH\Auth;

use Kraken\SSH\SSH2AuthInterface;

/**
 * Public key based SSH2 authentication
 */
class SSH2PublicKeyFile implements SSH2AuthInterface
{
    /**
     * @var string
     */
    protected $username;

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
     * @param string $username The authentication username
     * @param string $publicKeyFile The path of the public key file
     * @param string $privateKeyFile The path of the private key file
     * @param string|null $passPhrase An optional pass phrase for the key
     */
    public function __construct($username, $publicKeyFile, $privateKeyFile, $passPhrase = null)
    {
        $this->username = $username;
        $this->publicKeyFile = $publicKeyFile;
        $this->privateKeyFile = $privateKeyFile;
        $this->passPhrase = $passPhrase;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function authenticate($conn)
    {
        return @ssh2_auth_pubkey_file(
            $conn,
            $this->username,
            $this->publicKeyFile,
            $this->privateKeyFile,
            $this->passPhrase
        );
    }
}
