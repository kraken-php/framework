<?php

namespace Kraken\Transfer\Http\Component\Router;

use Kraken\Transfer\Http\HttpRequestInterface;
use Kraken\Transfer\Http\HttpResponse;
use Kraken\Transfer\ServerComponentAwareInterface;
use Kraken\Transfer\TransferConnectionInterface;
use Kraken\Transfer\TransferMessageInterface;
use Kraken\Transfer\ServerComponentInterface;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Error;
use Exception;

class HttpRouter implements HttpRouterInterface
{
    /**
     * @var RouteCollection
     */
    protected $routes;

    /**
     * @var RequestContext
     */
    protected $context;

    /**
     * @var UrlMatcherInterface
     */
    protected $matcher;

    /**
     * @var string[]
     */
    protected $allowedOrigins;

    /**
     * @param ServerComponentAwareInterface $aware
     * @param RouteCollection|null $routes
     * @param RequestContext|null $context
     */
    public function __construct(ServerComponentAwareInterface $aware = null, RouteCollection $routes = null, RequestContext $context = null)
    {
        $this->routes = ($routes !== null) ? $routes : new RouteCollection();
        $this->context = ($context !== null) ? $context : new RequestContext();
        $this->matcher = new UrlMatcher(
            $this->routes,
            $this->context
        );
        $this->allowedOrigins = [];

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
        unset($this->routes);
        unset($this->matcher);
        unset($this->matcher);
        unset($this->allowedOrigins);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function blockAddress($address)
    {
        $this->allowedOrigins[$address] = true;

        return $this;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function unblockAddress($address)
    {
        if (isset($this->allowedOrigins[$address]))
        {
            unset($this->allowedOrigins[$address]);
        }

        return $this;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isBlocked($address)
    {
        return isset($this->allowedOrigins[$address]);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getBlockedAddresses()
    {
        return array_keys($this->allowedOrigins);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function addRoute($path, ServerComponentInterface $component)
    {
        $this->routes->add(
            $path,
            new Route(
                $path,
                [ '_controller' => $component ],
                [ 'Origin' => 'localhost' ],
                [],
                'localhost'
            )
        );

        return $this;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function removeRoute($path)
    {
        $this->routes->remove($path);

        return $this;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function handleConnect(TransferConnectionInterface $conn)
    {}

    /**
     * @override
     * @inheritDoc
     */
    public function handleDisconnect(TransferConnectionInterface $conn)
    {
        if (isset($conn->controller))
        {
            $conn->controller->handleDisconnect($conn);
        }
    }

    /**
     * @override
     * @inheritDoc
     */
    public function handleMessage(TransferConnectionInterface $conn, TransferMessageInterface $message)
    {
        if (!$message instanceof HttpRequestInterface)
        {
            $conn->controller->handleMessage($conn, $message);
            return;
        }

        if (($header = $message->getHeaderLine('Origin')) !== '')
        {
            $origin = parse_url($header, PHP_URL_HOST) ?: $header;

            if ($origin !== '' && $this->isBlocked($origin))
            {
                return $this->close($conn, 403);
            }
        }

        $context = $this->matcher->getContext();
        $context->setMethod($message->getMethod());
        $context->setHost($message->getUri()->getHost());
        $route = [];

        try
        {
            $route = $this->matcher->match($message->getUri()->getPath());
        }
        catch (Error $ex)
        {
            return $this->close($conn, 500);
        }
        catch (MethodNotAllowedException $nae)
        {
            return $this->close($conn, 403);
        }
        catch (ResourceNotFoundException $nfe)
        {
            return $this->close($conn, 404);
        }

        $conn->controller = $route['_controller'];

        try
        {
            $conn->controller->handleConnect($conn);
            $conn->controller->handleMessage($conn, $message);
        }
        catch (Error $ex)
        {
            $conn->controller->handleError($conn, $ex);
        }
        catch (Exception $ex)
        {
            $conn->controller->handleError($conn, $ex);
        }
    }

    /**
     * @override
     * @inheritDoc
     */
    public function handleError(TransferConnectionInterface $conn, $ex)
    {
        if (isset($conn->controller))
        {
            try
            {
                return $conn->controller->handleError($conn, $ex);
            }
            catch (Error $ex)
            {}
            catch (Exception $ex)
            {}
        }

        $this->close($conn, 500);
    }

    /**
     * Close a connection with an HTTP response.
     *
     * @param TransferConnectionInterface $conn
     * @param int $code
     * @return null
     */
    protected function close(TransferConnectionInterface $conn, $code = 400)
    {
        $response = new HttpResponse($code);

        $conn->send($response);
        $conn->close();
    }
}
