<?php

namespace Kraken\Channel;

class Channel
{
    /**
     * @var string
     */
    const TYPE_SND = 'SND';

    /**
     * @var string
     */
    const TYPE_REQ = 'REQ';

    /**
     * @var int
     */
    const MODE_DEFAULT = 0;

    /**
     * @var int
     */
    const MODE_STANDARD = 0;

    /**
     * @var int
     */
    const MODE_BUFFER_ONLINE = 1;

    /**
     * @var int
     */
    const MODE_BUFFER_OFFLINE = 2;

    /**
     * @var int
     */
    const MODE_BUFFER = 3;
}
