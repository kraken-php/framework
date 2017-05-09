<?php

namespace Kraken\Network\Websocket\Driver\Version;

use Kraken\Network\Http\HttpRequestInterface;

class VersionManager implements VersionManagerInterface
{
    /**
     * @var string
     */
    protected $versionHeader;

    /**
     * @var VersionInterface[]
     */
    protected $versionCollection;

    /**
     *
     */
    public function __construct()
    {
        $this->versionHeader = '';
        $this->versionCollection = [];
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->versionHeader);
        unset($this->versionCollection);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getVersion(HttpRequestInterface $request)
    {
        foreach ($this->versionCollection as $version)
        {
            if ($version->isRequestSupported($request) === true)
            {
                return $version;
            }
        }

        return null;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function checkVersion(HttpRequestInterface $request)
    {
        return $this->getVersion($request) !== null;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function enableVersion(VersionInterface $version)
    {
        $this->versionCollection[$version->getVersionNumber()] = $version;

        $this->updateVersionHeader();

        return $this;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function disableVersion(VersionInterface $version)
    {
        unset($this->versionCollection[$version->getVersionNumber()]);

        $this->updateVersionHeader();

        return $this;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getVersionHeader()
    {
        return $this->versionHeader;
    }

    /**
     * Update current supported versions header.
     */
    protected function updateVersionHeader()
    {
        $this->versionHeader = implode(',', array_keys($this->versionCollection));
    }
}
