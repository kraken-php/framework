<?php

namespace Kraken\_Unit\Supervisor\_Aware;

use Kraken\Supervisor\SupervisorAwareInterface;
use Kraken\Supervisor\SupervisorAwareTrait;

class SupervisorAwareObject implements SupervisorAwareInterface
{
    use SupervisorAwareTrait;
}
