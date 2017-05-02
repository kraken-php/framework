<?php

namespace Kraken\SSH;

use Kraken\Event\BaseEventEmitterTrait;
use Kraken\Loop\LoopAwareTrait;
use Kraken\Loop\LoopInterface;
use Kraken\SSH\Driver\Sftp;
use Kraken\SSH\Driver\Shell;
use Kraken\Throwable\Exception\Logic\InvalidArgumentException;
use Kraken\Throwable\Exception\Runtime\ExecutionException;
use Error;
use Exception;

class SSH2 implements SSH2Interface
{
    use BaseEventEmitterTrait;
    use LoopAwareTrait;

    /**
     * @var string
     */
    const DRIVER_SHELL = 'shell';

    /**
     * @var string
     */
    const DRIVER_SFTP = 'sftp';

    /**
     * @var SSH2AuthInterface
     */
    protected $auth;

    /**
     * @var SSH2ConfigInterface
     */
    protected $config;

    /**
     * @var resource
     */
    protected $conn;

    /**
     * @var SSH2DriverInterface[]
     */
    protected $drivers;

    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port;

    /**
     * @var mixed
     */
    private $methods;

    /**
     * Constructor.
     *
     * @param SSH2AuthInterface $auth
     * @param SSH2ConfigInterface $config
     * @param LoopInterface $loop
     */
    public function __construct(SSH2AuthInterface $auth, SSH2ConfigInterface $config, LoopInterface $loop)
    {
        $this->auth = $auth;
        $this->config = $config;
        $this->loop = $loop;

        $this->host = $config->getHost();
        $this->port = $config->getPort();
        $this->methods = $config->getMethods();

        $this->conn = null;
        $this->drivers = [];
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->disconnect();
        $this->destructEventEmitterTrait();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function connect()
    {
        if ($this->conn !== null)
        {
            return;
        }

        $this->conn = $this->createConnection($this->host, $this->port, $this->methods, []);

        if (!$this->conn || !is_resource($this->conn))
        {
            $this->emit('error', [ $this, new ExecutionException('SSH2 connection could not be established.') ]);
            return;
        }

        if (!$this->auth->authenticate($this->conn))
        {
            $this->emit('error', [ $this, new ExecutionException('SSH2 connection could not be authenticated.') ]);
            return;
        }

        $this->emit('connect', [ $this ]);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function disconnect()
    {
        if ($this->conn === null || !is_resource($this->conn))
        {
            return;
        }

        foreach ($this->drivers as $driver)
        {
            $driver->disconnect();
        }

        foreach ($this->drivers as $driver)
        {
            $driver->removeListener('connect', [ $this, 'handleConnect' ]);
            $driver->removeListener('disconnect', [ $this, 'handleDisconnect' ]);
            $driver->removeListener('error', [ $this, 'handleError' ]);
        }

        $this->conn = null;
        $this->drivers = [];

        $this->emit('disconnect', [ $this ]);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isConnected()
    {
        return $this->conn !== null && is_resource($this->conn);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function createDriver($name)
    {
        if (isset($this->drivers[$name]))
        {
            return $this->drivers[$name];
        }

        if (!$this->isConnected())
        {
            throw new ExecutionException("The driver can be created only after the connection has been established!");
        }

        switch ($name)
        {
            case self::DRIVER_SHELL:
                $driver = new Shell($this, $this->conn);
                break;

            case self::DRIVER_SFTP:
                $driver = new Sftp($this, $this->conn);
                break;

            default:
                throw new InvalidArgumentException("The driver [$name] is not supported.");
        }

        $driver->on('connect', [ $this, 'handleConnect' ]);
        $driver->on('disconnect', [ $this, 'handleDisconnect' ]);
        $driver->on('error', [ $this, 'handleError' ]);

        $this->drivers[$name] = $driver;

        return $driver;
    }

    /**
     * @internal
     * @param SSH2DriverInterface $driver
     */
    public function handleConnect(SSH2DriverInterface $driver)
    {
        $this->emit('connect:' . $driver->getName(), [ $driver ]);
    }

    /**
     * @internal
     * @param SSH2DriverInterface $driver
     */
    public function handleDisconnect(SSH2DriverInterface $driver)
    {
        $this->emit('disconnect:' . $driver->getName(), [ $driver ]);
    }

    /**
     * @internal
     * @param SSH2DriverInterface $driver
     * @param Error|Exception $ex
     */
    public function handleError(SSH2DriverInterface $driver, $ex)
    {
        $this->emit('error:' . $driver->getName(), [ $driver, $ex ]);
        $this->emit('error', [ $this, $ex ]);
    }

    /**
     * Create SSH2 connection.
     *
     * @param string $host
     * @param int $port
     * @param mixed[] $methods
     * @param callable[] $callbacks
     * @return resource
     */
    protected function createConnection($host, $port, $methods, $callbacks)
    {
        return @ssh2_connect($host, $port, $methods, $callbacks);
    }
}
