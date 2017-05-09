<?php

namespace Kraken\Network\Websocket\Driver\Version;

use Kraken\Util\Factory\Factory;
use Ratchet\WebSocket\Encoding\ValidatorInterface;

class VersionFactory extends Factory implements VersionFactoryInterface
{
    /**
     * @param ValidatorInterface|null $validator
     */
    public function __construct(ValidatorInterface $validator = null)
    {
        parent::__construct();

        $factory = $this;
        $factory
            ->bindParam('validator', $validator)
        ;
        $factory
            ->define('HyBi10',  function() use($factory) {
                return new  HyBi10\Version($factory->getParam('validator'));
            })
            ->define('RFC6455', function() use($factory) {
                return new RFC6455\Version($factory->getParam('validator'));
            })
        ;
    }
}
