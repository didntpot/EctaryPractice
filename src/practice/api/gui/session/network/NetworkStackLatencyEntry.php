<?php

namespace practice\api\gui\session\network;

use Closure;

final class NetworkStackLatencyEntry{

	/** @var int */
	public $timestamp;

	/** @var int */
	public $network_timestamp;

	/** @var Closure */
	public $then;

	public function __construct(int $timestamp, Closure $then, ?int $network_timestamp = null){
		$this->timestamp = $timestamp;
		$this->then = $then;
		$this->network_timestamp = $network_timestamp ?? $timestamp;
	}
}