<?php
declare(strict_types=1);

namespace DaPigGuy\PiggyShopUI\shops;

class ShopSubcategory extends ShopCategory
{
    private ?ShopCategory $parent = null;

    public function setParent(ShopCategory $parent): void {
        $this->parent = $parent;
    }

    public function getParent(): ?ShopCategory {
        return $this->parent;
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
            }, $data["items"]),
            array_map(function(array $subcategoryData): ShopSubcategory {
                return ShopSubcategory::deserialize($subcategoryData);
            }, $data["subcategories"] ?? []),
            $data["private"],
            $data["imageType"] ?? -1,
            $data["imagePath"] ?? ""
        );
    }
}