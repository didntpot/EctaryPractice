<?php

namespace practice\api\gui\metadata;

use practice\api\gui\session\MenuExtradata;
use pocketmine\block\Block;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\Player;

class SingleBlockMenuMetadata extends MenuMetadata{

	/** @var Block */
	protected $block;

	public function __construct(string $identifier, int $size, int $window_type, Block $block){
		parent::__construct($identifier, $size, $window_type);
		$this->block = $block;
	}

	public function sendGraphic(Player $player, MenuExtradata $metadata) : bool{
		$positions = $this->getBlockPositions($metadata);
		if(count($positions) > 0){
			foreach($positions as $pos){
				$this->sendGraphicAt($pos, $player, $metadata);
			}
			return true;
		}
		return false;
	}

	protected function sendGraphicAt(Vector3 $pos, Player $player, MenuExtradata $metadata) : void{
		$packet = new UpdateBlockPacket();
		$packet->x = $pos->x;
		$packet->y = $pos->y;
		$packet->z = $pos->z;
		$packet->blockRuntimeId = $this->block->getRuntimeId();
		$packet->flags = UpdateBlockPacket::FLAG_NETWORK;
		$player->sendDataPacket($packet);
	}

	public function removeGraphic(Player $player, MenuExtradata $extradata) : void{
		$level = $player->getLevel();
		foreach($this->getBlockPositions($extradata) as $pos){
			$packet = new UpdateBlockPacket();
			$packet->x = $pos->x;
			$packet->y = $pos->y;
			$packet->z = $pos->z;
			$packet->blockRuntimeId = $level->getBlockAt($pos->x, $pos->y, $pos->z)->getRuntimeId();
			$packet->flags = UpdateBlockPacket::FLAG_NETWORK;
			$player->sendDataPacket($packet, false, true);
		}
	}

	/**
	 * @param MenuExtradata $metadata
	 * @return Vector3[]
	 */
	protected function getBlockPositions(MenuExtradata $metadata) : array{
		$pos = $metadata->getPositionNotNull();
		return $pos->y >= 0 && $pos->y < Level::Y_MAX ? [$pos] : [];
	}
}