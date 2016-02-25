<?php

namespace Kraken\Io\Websocket\Driver;

use Kraken\Io\Http\HttpRequestInterface;
use Kraken\Io\Websocket\Driver\Version\HyBi10;
use Kraken\Io\Websocket\Driver\Version\RFC6455;
use Kraken\Io\Websocket\Driver\Version\VersionInterface;
use Kraken\Io\Websocket\Driver\Version\VersionManager;
use Kraken\Io\Websocket\Driver\Version\VersionManagerInterface;
use Ratchet\WebSocket\Encoding\ToggleableValidator;
use Ratchet\WebSocket\Encoding\ValidatorInterface;

class WsDriver implements WsDriverInterface
{
    /**
     * @var VersionManagerInterface
     */
    protected $versioner;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     *
     */
    public function __construct()
    {
        $this->versioner = new VersionManager();
        $this->validator = new ToggleableValidator();

        $this->versioner
            ->enableVersion(new RFC6455\Version($this->validator))
            ->enableVersion(new HyBi10\Version($this->validator))
        ;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->versioner);
        unset($this->validator);
    }

    /**
     * @override
     */
    public function setEncodingChecks($opt)
    {
        $this->validator->on = (boolean)$opt;

        return $this;
    }


    /**
     * @override
     */
    public function getVersion(HttpRequestInterface $request)
    {
        return $this->versioner->getVersion($request);
    }

    /**
     * @override
     */
    public function isVersionEnabled(HttpRequestInterface $request)
    {
        return $this->versioner->isVersionEnabled($request);
    }

    /**
     * @override
     */
    public function enableVersion(VersionInterface $version)
    {
        return $this->versioner->enableVersion($version);
    }

    /**
     * @override
     */
    public function disableVersion(VersionInterface $version)
    {
        return $this->versioner->disableVersion($version);
    }

    /**
     * @override
     */
    public function getVersionHeader()
    {
        return $this->versioner->getVersionHeader();
    }
}
