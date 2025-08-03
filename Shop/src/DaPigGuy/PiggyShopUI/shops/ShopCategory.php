<?php
declare(strict_types=1);

namespace DaPigGuy\PiggyShopUI\shops;

use DaPigGuy\PiggyShopUI\PiggyShopUI;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\nbt\BigEndianNbtSerializer;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;

class ShopCategory
{
    public function __construct(
        private string $name,
        private array $items, // @var ShopItem[]
        private array $subcategories, // @var ShopSubcategory[]
        private bool $private,
        private int $imageType,
        private string $imagePath
    ) {
        foreach ($this->subcategories as $subcategory) {
            $subcategory->setParent($this);
        }
        $this->registerPermission();
    }

    private function registerPermission(): void {
        $node = "piggyshopui.category." . strtolower($this->name);
        PermissionManager::getInstance()->addPermission(
            new Permission($node, "Allows usage of the $this->name category")
        );
        PermissionManager::getInstance()->getPermission("piggyshopui.category")?->addChild($node, true);
    }

    public function getName(): string { return $this->name; }
    public function isPrivate(): bool { return $this->private; }
    public function getImageType(): int { return $this->imageType; }
    public function getImagePath(): string { return $this->imagePath; }
    public function getItems(): array { return $this->items; }
    public function getSubCategories(): array { return $this->subcategories; }

    public function addItem(ShopItem $item): void {
        $this->items[] = $item;
        PiggyShopUI::getInstance()->saveToShopConfig();
    }

    public function removeItem(int $index): void {
        if (isset($this->items[$index])) {
            unset($this->items[$index]);
            $this->items = array_values($this->items);
            PiggyShopUI::getInstance()->saveToShopConfig();
        }
    }

    public function getItem(int $index): ?ShopItem {
        return $this->items[$index] ?? null;
    }

    public function addSubCategory(ShopSubcategory $subcategory): void {
        $this->subcategories[] = $subcategory;
        PiggyShopUI::getInstance()->saveToShopConfig();
    }

    public function removeSubCategory(int $index): void {
        if (isset($this->subcategories[$index])) {
            unset($this->subcategories[$index]);
            $this->subcategories = array_values($this->subcategories);
            PiggyShopUI::getInstance()->saveToShopConfig();
        }
    }

    public function setName(string $name): void {
        $this->name = $name;
        PiggyShopUI::getInstance()->saveToShopConfig();
    }

    public function setPrivate(bool $private): void {
        $this->private = $private;
        PiggyShopUI::getInstance()->saveToShopConfig();
    }

    public function setImageType(int $imageType): void {
        $this->imageType = $imageType;
        PiggyShopUI::getInstance()->saveToShopConfig();
    }

    public function setImagePath(string $imagePath): void {
        $this->imagePath = $imagePath;
        PiggyShopUI::getInstance()->saveToShopConfig();
    }

    public function serialize(): array {
        return [
            "name" => $this->name,
            "items" => array_map(fn(ShopItem $item) => $item->serialize(), $this->items),
            "subcategories" => array_map(fn(ShopSubcategory $sub) => $sub->serialize(), $this->subcategories),
            "private" => $this->private,
            "imageType" => $this->imageType,
            "imagePath" => $this->imagePath
        ];
    }

    public static function deserialize(array $data): self {
        return new self(
            $data["name"],
            array_map(function(array $itemData): ShopItem {
                return new ShopItem(
                    self::deserializeItem($itemData["item"]),
                    $itemData["description"],
                    $itemData["canBuy"] ?? true,
                    $itemData["buyPrice"],
                    $itemData["canSell"] ?? false,
                    $itemData["sellPrice"] ?? 0,
                    $itemData["imageType"] ?? -1,
                    $itemData["imagePath"] ?? ""
                );
            }, $data["items"] ?? []),
            array_map(function(array $subcategoryData): ShopSubcategory {
                return ShopSubcategory::deserialize($subcategoryData);
            }, $data["subcategories"] ?? []),
            $data["private"],
            $data["imageType"] ?? -1,
            $data["imagePath"] ?? ""
        );
    }

    private static function deserializeItem(array $data): Item {
        $itemIdRaw = $data["id"] ?? "minecraft:stone";
        $itemId = is_int($itemIdRaw) ? (string)$itemIdRaw : $itemIdRaw;

        if (is_numeric($itemIdRaw)) {
            PiggyShopUI::getInstance()->getLogger()->warning("Legacy numeric ID '$itemIdRaw' used. Convert to string IDs.");
        }

        $item = StringToItemParser::getInstance()->parse($itemId);

        if ($item === null) {
            PiggyShopUI::getInstance()->getLogger()->error("Unable to resolve item ID '$itemId'. Falling back to minecraft:stone.");
            $item = StringToItemParser::getInstance()->parse("minecraft:stone");
        }

        return self::finalizeItem($item, $data);
    }

    private static function finalizeItem(Item $item, array $data): Item {
        $item->setCount($data["count"] ?? 1);

        if (isset($data["meta"]) && $data["meta"] > 0) {
            $item->setDamage($data["meta"]);
        }

        if (!empty($data["nbt"])) {
            try {
                $tag = (new BigEndianNbtSerializer())->read($data["nbt"]);
                if ($tag instanceof CompoundTag) {
                    $item->setNamedTag($tag);
                }
            } catch (\Throwable $e) {
                PiggyShopUI::getInstance()->getLogger()->error("Failed to parse NBT: " . $e->getMessage());
            }
        }

        return $item;
    }
}
