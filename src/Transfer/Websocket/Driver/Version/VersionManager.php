<?php

namespace Kraken\Transfer\Websocket\Driver\Version;

use Kraken\Transfer\Http\HttpRequestInterface;

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
     */
    public function isVersionEnabled(HttpRequestInterface $request)
    {
        return $this->getVersion($request) !== null;
    }

    /**
     * @override
     */
    public function enableVersion(VersionInterface $version)
    {
        $this->versionCollection[$version->getVersionNumber()] = $version;

        $this->updateVersionHeader();

        return $this;
    }

    /**
     * @override
     */
    public function disableVersion(VersionInterface $version)
    {
        unset($this->versionCollection[$version->getVersionNumber()]);

        $this->updateVersionHeader();

        return $this;
    }

    /**
     * @override
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
