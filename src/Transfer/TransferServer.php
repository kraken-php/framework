<?php

namespace Kraken\Transfer;

use Kraken\Ipc\Socket\SocketListenerInterface;
use Kraken\Loop\LoopInterface;
use Kraken\Throwable\Exception\Logic\InstantiationException;
use Kraken\Transfer\Http\Component\Router\HttpRouter;
use Kraken\Transfer\Http\Component\Router\HttpRouterInterface;
use Kraken\Transfer\Http\HttpServer;
use Kraken\Transfer\Socket\Component\Firewall\SocketFirewall;
use Kraken\Transfer\Socket\SocketServer;
use Kraken\Transfer\Socket\SocketServerInterface;
use Error;
use Exception;

class TransferServer implements TransferServerInterface
{
    /**
     * @var SocketListenerInterface
     */
    protected $listener;

    /**
     * @var SocketServerInterface
     */
    protected $server;

    /**
     * @var SocketFirewall
     */
    protected $firewall;

    /**
     * @var HttpRouterInterface
     */
    protected $router;

    /**
     * @param SocketListenerInterface $listener
     * @throws InstantiationException
     */
    public function __construct(SocketListenerInterface $listener)
    {
        try
        {
            $router = new HttpRouter(
                $http = new HttpServer(
                    $firewall = new SocketFirewall(
                        $server = new SocketServer($listener)
                    )
                )
            );

            $this->listener = $listener;
            $this->server = $server;
            $this->firewall = $firewall;
            $this->router = $router;
        }
        catch (Error $ex)
        {
            throw new InstantiationException("[" . __CLASS__ . "] could not be created.", $ex);
        }
        catch (Exception $ex)
        {
            throw new InstantiationException("[" . __CLASS__ . "] could not be created.", $ex);
        }
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->router);
        unset($this->firewall);
        unset($this->server);
        unset($this->listener);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function addRoute($path, ServerComponentInterface $component)
    {
        return $this->router->addRoute($path, $component);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function removeRoute($path)
    {
        return $this->router->removeRoute($path);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function blockAddress($address)
    {
        $this->firewall->blockAddress($address);

        return $this;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function unblockAddress($address)
    {
        $this->firewall->unblockAddress($address);

        return $this;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isAddressBlocked($address)
    {
        return $this->firewall->isAddressBlocked($address);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getBlockedAddresses()
    {
        return $this->firewall->getBlockedAddresses();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function stop()
    {
        $this->listener->close();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function close()
    {
        $this->listener->close();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getLoop()
    {
        return $this->listener->getLoop();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function setLoop(LoopInterface $loop)
    {
        $this->listener->setLoop($loop);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isPaused()
    {
        return $this->listener->isPaused();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function pause()
    {
        $this->listener->pause();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function resume()
    {
        $this->listener->resume();
    }
}
