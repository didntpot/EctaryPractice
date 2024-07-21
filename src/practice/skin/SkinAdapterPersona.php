<?php

namespace practice\skin;

use practice\loader\SkinLoader;
use pocketmine\entity\Skin;
use pocketmine\network\mcpe\protocol\types\LegacySkinAdapter;
use pocketmine\network\mcpe\protocol\types\SkinData;

class SkinAdapterPersona extends LegacySkinAdapter
{
    public function fromSkinData(SkinData $data): Skin
    {
        if($data->isPersona())
        {
            return SkinLoader::getRandomSkin();
        }

        return parent::fromSkinData($data);
    }

}