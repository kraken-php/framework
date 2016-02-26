<?php

namespace Kraken\Io\Http\Component\Session;

use Kraken\Throwable\Exception\RuntimeException;
use Kraken\Io\IoConnectionInterface;
use Kraken\Io\IoMessageInterface;
use Kraken\Io\IoServerComponentInterface;
use Ratchet\Session\Serialize\HandlerInterface;
use Ratchet\Session\Storage\VirtualSessionStorage;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NullSessionHandler;
use Exception;
use SessionHandlerInterface;

class HttpSession implements HttpSessionInterface
{
    /**
     * @var IoServerComponentInterface
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
     * @param IoServerComponentInterface $component
     * @param SessionHandlerInterface $handler
     * @param string[] $options
     * @param HandlerInterface $serializer
     * @throws RuntimeException
     */
    public function __construct(IoServerComponentInterface $component, SessionHandlerInterface $handler, $options = [], HandlerInterface $serializer = null)
    {
        $this->component = $component;
        $this->handler = $handler;
        $this->nullHandler = new NullSessionHandler();

        ini_set('session.auto_start', 0);
        ini_set('session.cache_limiter', '');
        ini_set('session.use_cookies', 0);

        $this->setOptions($options);

        if ($serializer === null)
        {
            $serialClass = __NAMESPACE__ . "\\Serialize\\{$this->toClassCase(ini_get('session.serialize_handler'))}Handler";
            if (!class_exists($serialClass))
            {
                throw new RuntimeException('Unable to parse session serialize handler.');
            }

            $serializer = new $serialClass;
        }

        $this->serializer = $serializer;
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
    public function handleConnect(IoConnectionInterface $conn)
    {
        if (!isset($conn->WebSocket) || null === ($id = $conn->WebSocket->request->getCookie(ini_get('session.name'))))
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
    public function handleDisconnect(IoConnectionInterface $conn)
    {
        return $this->component->handleDisconnect($conn);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function handleMessage(IoConnectionInterface $conn, IoMessageInterface $message)
    {
        return $this->component->handleMessage($conn, $message);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function handleError(IoConnectionInterface $conn, $ex)
    {
        return $this->component->handleError($conn, $ex);
    }

//    /**
//     * {@inheritdoc}
//     */
//    public function getSubProtocols() {
//        if ($this->_app instanceof WsServerInterface) {
//            return $this->_app->getSubProtocols();
//        } else {
//            return array();
//        }
//    }

    /**
     * Set all the php session. ini options.
     *
     * @param string[] $options
     * @return string[]
     */
    protected function setOptions($options)
    {
        $all = [
            'auto_start', 'cache_limiter', 'cookie_domain', 'cookie_httponly',
            'cookie_lifetime', 'cookie_path', 'cookie_secure',
            'entropy_file', 'entropy_length', 'gc_divisor',
            'gc_maxlifetime', 'gc_probability', 'hash_bits_per_character',
            'hash_function', 'name', 'referer_check',
            'serialize_handler', 'use_cookies',
            'use_only_cookies', 'use_trans_sid', 'upload_progress.enabled',
            'upload_progress.cleanup', 'upload_progress.prefix', 'upload_progress.name',
            'upload_progress.freq', 'upload_progress.min-freq', 'url_rewriter.tags'
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
