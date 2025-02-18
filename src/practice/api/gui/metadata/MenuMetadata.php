<?php

namespace practice\api\gui\metadata;

use practice\api\gui\inventory\InvMenuInventory;
use practice\api\gui\session\MenuExtradata;
use pocketmine\math\Vector3;
use pocketmine\Player;

abstract class MenuMetadata{

	/** @var string */
	protected $identifier;

	/** @var int */
	protected $size;

	/** @var int */
	protected $window_type;

	public function __construct(string $identifier, int $size, int $window_type){
		$this->identifier = $identifier;
		$this->size = $size;
		$this->window_type = $window_type;
	}

	public function getIdentifier() : string{
		return $this->identifier;
	}

	public function getSize() : int{
		return $this->size;
	}

	public function getWindowType() : int{
		return $this->window_type;
	}

	public function createInventory() : InvMenuInventory{
		return new InvMenuInventory($this);
	}

	protected function calculateGraphicOffset(Player $player) : Vector3{
		$offset = $player->getDirectionVector();
		$offset->x *= -(1 + $player->width);
		$offset->y *= -(1 + $player->height);
		$offset->z *= -(1 + $player->width);
		return $offset;
	}

	public function calculateGraphicPosition(Player $player) : Vector3{
		return $player->getPosition()->add($this->calculateGraphicOffset($player))->floor();
	}

	abstract public function sendGraphic(Player $player, MenuExtradata $metadata) : bool;

	abstract public function removeGraphic(Player $player, MenuExtradata $extradata) : void;
}