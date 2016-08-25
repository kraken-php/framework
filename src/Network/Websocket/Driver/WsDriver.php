<?php

namespace Kraken\Network\Websocket\Driver;

use Kraken\Network\Http\HttpRequestInterface;
use Kraken\Network\Websocket\Driver\Version\HyBi10;
use Kraken\Network\Websocket\Driver\Version\RFC6455;
use Kraken\Network\Websocket\Driver\Version\VersionInterface;
use Kraken\Network\Websocket\Driver\Version\VersionManager;
use Kraken\Network\Websocket\Driver\Version\VersionManagerInterface;
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
            ->enableVersion(new  HyBi10\Version($this->validator))
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
     * @inheritDoc
     */
    public function setEncodingChecks($opt)
    {
        $this->validator->on = (boolean)$opt;

        return $this;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getVersion(HttpRequestInterface $request)
    {
        return $this->versioner->getVersion($request);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function checkVersion(HttpRequestInterface $request)
    {
        return $this->versioner->checkVersion($request);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function enableVersion(VersionInterface $version)
    {
        return $this->versioner->enableVersion($version);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function disableVersion(VersionInterface $version)
    {
        return $this->versioner->disableVersion($version);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getVersionHeader()
    {
        return $this->versioner->getVersionHeader();
    }
}
