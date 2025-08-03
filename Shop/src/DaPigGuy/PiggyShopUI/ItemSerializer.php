<?php

namespace PiggyShopUI;

use pocketmine\item\Item;
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\nbt\tag\CompoundTag;

class ItemSerializer {
    public static function serialize(Item $item): array {
        return [
            'id' => $item->getId(),
            'meta' => $item->getMeta(),
            'count' => $item->getCount(),
            'nbt' => $item->getNamedTag()->toString(),
            'name' => $item->getVanillaName()
        ];
    }

    public static function unserialize(array $data): Item {
        $item = Item::get($data['id'], $data['meta'], $data['count'] ?? 1);
        
        if (!empty($data['nbt'])) {
            $tag = (new LittleEndianNbtSerializer())->read($data['nbt']);
            if ($tag instanceof CompoundTag) {
                $item->setNamedTag($tag);
            }
        }
        
        return $item;
    }
}