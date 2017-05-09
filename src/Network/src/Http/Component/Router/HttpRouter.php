<?php

namespace Kraken\Network\Http\Component\Router;

use Kraken\Network\Http\HttpRequestInterface;
use Kraken\Network\Http\HttpResponse;
use Kraken\Network\NetworkComponentAwareInterface;
use Kraken\Network\NetworkConnectionInterface;
use Kraken\Network\NetworkMessageInterface;
use Kraken\Network\NetworkComponentInterface;
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
     * @var string
     */
    protected $host;

    /**
     * @var bool
     */
    protected $checkOrigin;

    /**
     * @var string[]
     */
    protected $allowedOrigins;

    /**
     * @param NetworkComponentAwareInterface $aware
     * @param mixed[] $params
     */
    public function __construct(NetworkComponentAwareInterface $aware = null, $params = [])
    {
        $this->routes = (isset($params['routes']) && $params['routes'] instanceof RouteCollection)
            ? $params['routes']
            : new RouteCollection();
        $this->context = (isset($params['context']) && $params['context'] instanceof RequestContext)
            ? $params['context']
            : new RequestContext();

        $this->matcher = new UrlMatcher(
            $this->routes,
            $this->context
        );

        $this->host = isset($params['host']) ? $params['host'] : 'localhost';
        $this->checkOrigin = isset($params['checkOrigin']) ? $params['checkOrigin'] : false;
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
        unset($this->params);
        unset($this->checkOrigin);
        unset($this->allowedOrigins);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function allowOrigin($address)
    {
        $this->allowedOrigins[$address] = true;

        return $this;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function disallowOrigin($address)
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
    public function isOriginAllowed($address)
    {
        return isset($this->allowedOrigins[$address]);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getAllowedOrigins()
    {
        return array_keys($this->allowedOrigins);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function existsRoute($path)
    {
        return $this->routes->get($path) !== null;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function addRoute($path, NetworkComponentInterface $component)
    {
        $this->routes->add(
            $path,
            new Route(
                $path,
                [ '_controller' => $component ],
                $this->checkOrigin ? [ 'Origin' => $this->host ] : [],
                [],
                $this->checkOrigin ? $this->host : ''
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
    public function handleConnect(NetworkConnectionInterface $conn)
    {}

    /**
     * @override
     * @inheritDoc
     */
    public function handleDisconnect(NetworkConnectionInterface $conn)
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
    public function handleMessage(NetworkConnectionInterface $conn, NetworkMessageInterface $message)
    {
        if (!$message instanceof HttpRequestInterface)
        {
            if (!isset($conn->controller))
            {
                return $this->close($conn, 500);
            }

            return $conn->controller->handleMessage($conn, $message);
        }

        if ($this->checkOrigin && ($header = $message->getHeaderLine('Origin')) !== '')
        {
            $origin = parse_url($header, PHP_URL_HOST) ?: $header;

            if ($origin !== '' && !$this->isOriginAllowed($origin))
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
        catch (MethodNotAllowedException $nae)
        {
            return $this->close($conn, 403);
        }
        catch (ResourceNotFoundException $nfe)
        {
            return $this->close($conn, 404);
        }
        catch (Error $ex)
        {
            return $this->close($conn, 500);
        }
        catch (Exception $ex)
        {
            return $this->close($conn, 500);
        }

        $conn->controller = $route['_controller'];

        try
        {
            $conn->controller->handleConnect($conn);
            $conn->controller->handleMessage($conn, $message);
            return;
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        $conn->controller->handleError($conn, $ex);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function handleError(NetworkConnectionInterface $conn, $ex)
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
     * @param NetworkConnectionInterface $conn
     * @param int $code
     * @return null
     */
    protected function close(NetworkConnectionInterface $conn, $code = 400)
    {
        $response = new HttpResponse($code);

        $conn->send($response);
        $conn->close();
    }
}
