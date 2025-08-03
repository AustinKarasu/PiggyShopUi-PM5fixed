<?php

namespace karasu;

use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\network\mcpe\protocol\types\command\CommandOverload;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase implements Listener {

    public function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getLogger()->info("ShopFixer enabled!");
    }

    public function onDataPacketSend(DataPacketSendEvent $event): void {
        foreach ($event->getPackets() as $packet) {
            if ($packet instanceof AvailableCommandsPacket) {
                $this->fixCommandPacket($packet);
            }
        }
    }

    private function fixCommandPacket(AvailableCommandsPacket $packet): void {
        $commandData = $packet->commandData;
        
        foreach ($commandData as &$command) {
            if (!isset($command->overloads)) continue;
            
            $fixedOverloads = [];
            foreach ($command->overloads as $overload) {
                // Skip if already properly formatted
                if ($overload instanceof CommandOverload) continue;
                
                $chaining = false;
                $parameters = [];
                
                if (is_array($overload)) {
                    $chaining = $overload['chaining'] ?? false;
                    $rawParams = $overload['parameters'] ?? [];
                    
                    foreach ($rawParams as $param) {
                        if ($param instanceof CommandParameter) {
                            $parameters[] = $param;
                        } elseif (is_array($param)) {
                            $parameters[] = new CommandParameter(
                                $param['paramName'] ?? '',
                                $param['paramType'] ?? 0,
                                $param['isOptional'] ?? false,
                                $param['flags'] ?? 0,
                                $param['enum'] ?? null,
                                $param['postfix'] ?? null
                            );
                        }
                    }
                }
                
                $fixedOverloads[] = new CommandOverload($chaining, $parameters);
            }
            
            $command->overloads = $fixedOverloads;
        }
    }
}