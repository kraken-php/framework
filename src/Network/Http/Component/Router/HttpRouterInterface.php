<?php

namespace Kraken\Network\Http\Component\Router;

use Kraken\Network\ServerComponentInterface;

interface HttpRouterInterface extends ServerComponentInterface
{
    /**
     * Add an origin to the whitelist that will be allowed to connect to your application.
     *
     * @param string $address
     * @return HttpRouterInterface
     */
    public function allowOrigin($address);

    /**
     * Remove an origin so it will not be able access your routes.
     *
     * @param string $address
     * @return HttpRouterInterface
     */
    public function disallowOrigin($address);

    /**
     * Check if given $address is allowed or not.
     *
     * @param string $address
     * @return bool
     */
    public function isOriginAllowed($address);

    /**
     * Get an array of all the addresses allowed.
     *
     * @return string[]
     */
    public function getAllowedOrigins();

    /**
     * Check if there exists any endpoint/application to the server on given route.
     *
     * @param string $path
     * @return bool
     */
    public function existsRoute($path);

    /**
     * Add an endpoint/application to the server.
     *
     * @param string $path
     * @param ServerComponentInterface $component
     * @return HttpRouterInterface
     */
    public function addRoute($path, ServerComponentInterface $component);

    /**
     * Remote endpoint/application from the server.
     *
     * @param string $path
     * @return HttpRouterInterface
     */
    public function removeRoute($path);
}
