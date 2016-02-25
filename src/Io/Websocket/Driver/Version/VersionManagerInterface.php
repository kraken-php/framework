<?php

namespace Kraken\Io\Websocket\Driver\Version;

use Kraken\Io\Http\HttpRequestInterface;

interface VersionManagerInterface
{
    /**
     * Get the protocol negotiator for the request, if supported.
     *
     * @param HttpRequestInterface $request
     * @return VersionInterface|null
     */
    public function getVersion(HttpRequestInterface $request);

    /**
     * Return true if any of enabled versions supports WebSocket protocol demanded in request.
     *
     * @param HttpRequestInterface $request
     * @return bool
     */
    public function isVersionEnabled(HttpRequestInterface $request);

    /**
     * Enable support for a specific version of the WebSocket protocol.
     *
     * @param VersionInterface $version
     * @return VersionManagerInterface
     */
    public function enableVersion(VersionInterface $version);

    /**
     * Disable support for a specific version of the WebSocket protocol.
     *
     * @param VersionInterface $version
     * @return VersionManagerInterface
     */
    public function disableVersion(VersionInterface $version);

    /**
     * @return string
     */
    public function getVersionHeader();
}
