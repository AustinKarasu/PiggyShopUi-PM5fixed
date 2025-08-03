<?php
declare(strict_types=1);

namespace DaPigGuy\PiggyShopUI\shops;

use DaPigGuy\PiggyShopUI\PiggyShopUI;
use pocketmine\item\Item;
use pocketmine\nbt\BigEndianNbtSerializer;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\item\ItemStateNames;

class ShopItem
{
    public function __construct(
        private Item $item,
        private string $description,
        private bool $canBuy,
        private float $buyPrice,
        private bool $canSell,
        private float $sellPrice,
        private int $imageType,
        private string $imagePath
    ) {}

    public function getItem(): Item { return $this->item; }
    public function getDescription(): string { return $this->description; }
    public function canBuy(): bool { return $this->canBuy; }
    public function getBuyPrice(): float { return $this->buyPrice; }
    public function canSell(): bool { return $this->canSell; }
    public function getSellPrice(): float { return $this->sellPrice; }
    public function getImageType(): int { return $this->imageType; }
    public function getImagePath(): string { return $this->imagePath; }

    public function setDescription(string $description): void {
        $this->description = $description;
        PiggyShopUI::getInstance()->saveToShopConfig();
    }

    public function setCanBuy(bool $canBuy): void {
        $this->canBuy = $canBuy;
        PiggyShopUI::getInstance()->saveToShopConfig();
    }

    public function setBuyPrice(float $buyPrice): void {
        $this->buyPrice = $buyPrice;
        PiggyShopUI::getInstance()->saveToShopConfig();
    }

    public function setCanSell(bool $canSell): void {
        $this->canSell = $canSell;
        PiggyShopUI::getInstance()->saveToShopConfig();
    }

    public function setSellPrice(float $sellPrice): void {
        $this->sellPrice = $sellPrice;
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
            "item" => $this->serializeItem($this->item),
            "description" => $this->description,
            "canBuy" => $this->canBuy,
            "buyPrice" => $this->buyPrice,
            "canSell" => $this->canSell,
            "sellPrice" => $this->sellPrice,
            "imageType" => $this->imageType,
            "imagePath" => $this->imagePath
        ];
    }

   private function serializeItem(Item $item): array {
    $nbt = "";
    if ($item->getNamedTag()->count() > 0) {
        $nbt = (new BigEndianNbtSerializer())->write(new CompoundTag("", [$item->getNamedTag()]));
    }

    // Try getMeta() first (PMMP 4), then getState() (early PMMP 5), default to 0
    $meta = 0;
    if (method_exists($item, "getMeta")) {
        $meta = $item->getMeta();
    } elseif (method_exists($item, "getState")) {
        $meta = $item->getState(ItemStateNames::DAMAGE) ?? 0;
    }

    return [
        "id" => $item->getTypeId(),
        "meta" => $meta,
        "count" => $item->getCount(),
        "nbt" => $nbt
      ];
    }
}