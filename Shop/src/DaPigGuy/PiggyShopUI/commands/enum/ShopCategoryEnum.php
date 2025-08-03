<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyShopUI\commands\enum;

use CortexPE\Commando\args\StringEnumArgument;
use DaPigGuy\PiggyShopUI\PiggyShopUI;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class ShopCategoryEnum extends StringEnumArgument
{
    public function parse(string $argument, CommandSender $sender): ?\DaPigGuy\PiggyShopUI\shops\ShopCategory {
        $category = PiggyShopUI::getInstance()->getShopCategory($argument);
        if ($category !== null && ($sender instanceof Player)) {
            if ($category->isPrivate() && !$sender->hasPermission("piggyshopui.category." . strtolower($category->getName()))) {
                return null;
            }
        }
        return $category;
    }

    public function getEnumName(): string {
        return "category";
    }

    public function getEnumValues(): array {
        return array_values(array_map(function (\DaPigGuy\PiggyShopUI\shops\ShopCategory $category): string {
            return $category->getName();
        }, PiggyShopUI::getInstance()->getShopCategories()));
    }
    
    public function getTypeName(): string {
        return "category";
    }

    public function canParse(string $testString, CommandSender $sender): bool {
        $category = PiggyShopUI::getInstance()->getShopCategory($testString);
        if ($category === null) return false;
        
        if ($sender instanceof Player && $category->isPrivate()) {
            return $sender->hasPermission("piggyshopui.category." . strtolower($category->getName()));
        }
        return true;
    }
}