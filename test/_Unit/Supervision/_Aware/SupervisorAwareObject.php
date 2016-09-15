<?php

namespace Kraken\_Unit\Supervision\_Aware;

use Kraken\Supervision\SupervisorAwareInterface;
use Kraken\Supervision\SupervisorAwareTrait;

class SupervisorAwareObject implements SupervisorAwareInterface
{
    use SupervisorAwareTrait;
}
