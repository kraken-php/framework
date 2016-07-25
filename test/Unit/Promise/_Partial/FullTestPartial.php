<?php

namespace Kraken\Test\Unit\Promise\_Partial;

trait FullTestPartial
{
    use ApiResolvePartial;
    use ApiRejectPartial;
    use ApiCancelPartial;
    use PromisePendingPartial;
    use PromiseFulfilledPartial;
    use PromiseRejectedPartial;
    use PromiseCancelledPartial;
}
