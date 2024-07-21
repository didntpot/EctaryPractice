<?php

namespace practice\api\gui\session\network\handler;

use Closure;
use practice\api\gui\session\network\NetworkStackLatencyEntry;

interface PlayerNetworkHandler{

	public function createNetworkStackLatencyEntry(Closure $then) : NetworkStackLatencyEntry;
}