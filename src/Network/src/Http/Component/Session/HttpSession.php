<?php

namespace Kraken\Network\Http\Component\Session;

use Kraken\Network\Null\NullServer;
use Kraken\Network\NetworkComponentAwareInterface;
use Kraken\Network\NetworkComponentInterface;
use Kraken\Network\NetworkConnectionInterface;
use Kraken\Network\NetworkMessageInterface;
use Dazzle\Throwable\Exception\RuntimeException;
use Ratchet\Session\Serialize\HandlerInterface;
use Ratchet\Session\Storage\VirtualSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NullSessionHandler;
use Symfony\Component\HttpFoundation\Session\Session;
use SessionHandlerInterface;

class HttpSession implements HttpSessionInterface, NetworkComponentAwareInterface
{
    /**
     * @var NetworkComponentInterface
     */
    protected $component;

    /**
     * @var SessionHandlerInterface
     */
    protected $handler;

    /**
     * @var SessionHandlerInterface
     */
    protected $nullHandler;

    /**
     * @var HandlerInterface
     */
    protected $serializer;

    /**
     * @param NetworkComponentAwareInterface|null $aware
     * @param NetworkComponentInterface|null $component
     * @param SessionHandlerInterface|null $handler
     * @param string[] $options
     * @param HandlerInterface|null $serializer
     * @throws RuntimeException
     */
    public function __construct(
        NetworkComponentAwareInterface $aware = null,
        NetworkComponentInterface $component = null,
        SessionHandlerInterface $handler = null,
        $options = [],
        HandlerInterface $serializer = null
    ){
        $this->component = $component;
        $this->handler = $handler !== null ? $handler : new NullSessionHandler;
        $this->nullHandler = new NullSessionHandler();

        ini_set('session.auto_start', 0);
        ini_set('session.cache_limiter', '');
        ini_set('session.use_cookies', 0);

        $this->setOptions($options);

        if ($serializer === null)
        {
            $serialClass = "\\Ratchet\\Session\\Serialize\\{$this->toClassCase(ini_get('session.serialize_handler'))}Handler";

            if (!class_exists($serialClass))
            {
                throw new RuntimeException('Unable to parse session serialize handler.');
            }

            $serializer = new $serialClass;
        }

        $this->serializer = $serializer;

        if ($aware !== null)
        {
            $aware->setComponent($this);
        }
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->component);
        unset($this->handler);
        unset($this->nullHandler);
        unset($this->serializer);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function setComponent(NetworkComponentInterface $component = null)
    {
        $this->component = $component === null ? new NullServer() : $component;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getComponent()
    {
        return $this->component;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function handleConnect(NetworkConnectionInterface $conn)
    {
        if (!isset($conn->WebSocket) || ($id = $conn->WebSocket->request->getCookie(ini_get('session.name'))) === null)
        {
            $saveHandler = $this->nullHandler;
            $id = '';
        }
        else
        {
            $saveHandler = $this->handler;
        }

        $conn->Session = new Session(new VirtualSessionStorage($saveHandler, $id, $this->serializer));

        if (ini_get('session.auto_start'))
        {
            $conn->Session->start();
        }

        return $this->component->handleConnect($conn);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function handleDisconnect(NetworkConnectionInterface $conn)
    {
        return $this->component->handleDisconnect($conn);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function handleMessage(NetworkConnectionInterface $conn, NetworkMessageInterface $message)
    {
        return $this->component->handleMessage($conn, $message);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function handleError(NetworkConnectionInterface $conn, $ex)
    {
        return $this->component->handleError($conn, $ex);
    }

    /**
     * Set all the php session. ini options.
     *
     * @param string[] $options
     * @return string[]
     */
    protected function setOptions($options)
    {
        $all = [
            'auto_start',
            'cache_limiter',
            'cookie_domain',
            'cookie_httponly',
            'cookie_lifetime',
            'cookie_path',
            'cookie_secure',
            'entropy_file',
            'entropy_length',
            'gc_divisor',
            'gc_maxlifetime',
            'gc_probability',
            'hash_bits_per_character',
            'hash_function',
            'name',
            'referer_check',
            'serialize_handler',
            'use_cookies',
            'use_only_cookies',
            'use_trans_sid',
            'upload_progress.enabled',
            'upload_progress.cleanup',
            'upload_progress.prefix',
            'upload_progress.name',
            'upload_progress.freq',
            'upload_progress.min-freq',
            'url_rewriter.tags'
        ];

        foreach ($all as $key)
        {
            if (!array_key_exists($key, $options))
            {
                $options[$key] = ini_get("session.{$key}");
            }
            else
            {
                ini_set("session.{$key}", $options[$key]);
            }
        }

        return $options;
    }

    /**
     * @param string $langDef Input to convert
     * @return string
     */
    protected function toClassCase($langDef)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $langDef)));
    }
}
