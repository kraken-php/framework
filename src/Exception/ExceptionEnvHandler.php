<?php

namespace Kraken\Exception;

use Kraken\Pattern\Enum\EnumTrait;
use Kraken\Pattern\Enum\EnumInterface;

class ExceptionEnvHandler implements EnumInterface
{
    use EnumTrait;

    /**
     * @var int
     */
    const E_UNSUPPORTED = 8;

    /**
     * @var int
     */
    const E_ERROR = 4;

    /**
     * @var int
     */
    const E_WARNING = 2;

    /**
     * @var int
     */
    const E_NOTICE = 1;

    /**
     * @param \Error|\Exception $ex
     */
    public static function handleException($ex)
    {
        // TODO Kraken-11: prepare better uncaught exception callback
        echo (string) $ex;
    }
}
