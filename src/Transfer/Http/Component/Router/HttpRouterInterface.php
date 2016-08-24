<?php

namespace Kraken\Transfer\Http\Component\Router;

use Kraken\Transfer\ServerComponentInterface;
use Kraken\Transfer\Socket\Component\Firewall\SocketFirewallInterface;

interface HttpRouterInterface extends SocketFirewallInterface
{
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
