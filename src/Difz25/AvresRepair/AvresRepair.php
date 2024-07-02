<?php

namespace Difz25\AvresRepair;

use onebone\economyapi\EconomyAPI;
use pocketmine\item\Armor;
use pocketmine\item\Durable;
use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class AvresRepair extends PluginBase {

    protected EconomyAPI $eco;
    protected Config $configData;

    public function onEnable(): void {
        $this->eco = EconomyAPI::getInstance();
        $this->configData = new Config($this->getDataFolder() . "config.yml" . Config::YAML);
    }
    
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if($command->getName() === "repair"){
            if($sender instanceof Player){
                $maincmd = $this->getConfigData()->get("Setting.Command.Permission.main", true);
                if($maincmd === "true"){
                    if($sender->hasPermission($maincmd)){
                        if(count($args) < 1){
                            $sender->sendMessage("Usage: /repair <hand|rename|lore>");
                        }
                    }
                } elseif(count($args) < 1){
                    $sender->sendMessage("Usage: /repair <hand|rename|lore>");
                }
                
                switch ($args) {
                    case "all":
                        foreach($sender->getInventory()->getContents() as $slot => $item){
                            if($this->getEconomyAPI()->myMoney($sender) > round($this->getConfigData()->getNested("Setting.Price.repairall") * $item->getDamage(), 1)){
                                if(!($item instanceof Durable) || !$this->isItem($item))continue;
                                    if($item->getDamage() > 0){
                                        $sender->getInventory()->setItem($slot, $item->setDamage(0));
                                    }
                                } else {
                                $sender->sendMessage($this->getConfigData()->getNested("Setting.Message.RepairAll.no-money"));
                                }
                            }
                        foreach($sender->getOffHandInventory()->getContents() as $slot => $offHanditem){
                            if($this->getEconomyAPI()->myMoney($sender) > round($this->getConfigData()->getNested("Setting.Price.repairall") * $item->getDamage(), 1)){
                                if(!($offHanditem instanceof Durable) || !$this->isItem($offHanditem))continue;
                                if($offHanditem->getDamage() > 0){
                                    $sender->getOffHandInventory()->setItem($slot, $offHanditem->setDamage(0));
                                }
                            } else {
                                $sender->sendMessage($this->getConfigData()->getNested("Setting.Message.RepairAll.no-money"));
                                }
                        }
                        foreach($sender->getArmorInventory()->getContents() as $armorSlot => $armor){
                            if($this->getEconomyAPI()->myMoney($sender) > round($this->getConfigData()->getNested("Setting.Price.repairall") * $item->getDamage(), 1)){
                                if(!($armor instanceof Durable) || !$this->isItem($armor))continue;
                                if($armor->getDamage() > 0){
                                    $sender->getArmorInventory()->setItem($armorSlot, $armor->setDamage(0));
                                }
                            } else {
                                $sender->sendMessage($this->getConfigData()->getNested("Setting.Message.RepairAll.no-money"));
                                }
                        }
                        
                        $sender->sendMessage($this->getConfigData()->getNested("Setting.Message.RepairAll.completed-repairall"));
                        break;
                    case "hand":
                        $myMoney = $this->getEconomyAPI()->myMoney($sender);
                        $item = $sender->getInventory()->getItemInHand();
                        $price = $this->getConfigData()->getNested($this->getConfigData()->getNested("Setting.Price.repairhand"));
                        $total = round($price * $item->getDamage(), 1);
                        if ($item instanceof Tool || $item instanceof Armor) {
                            if ($myMoney > $total) {
                                $item->setDamage(0);
                            } else {
                                $sender->sendMessage($this->getConfigData()->getNested("Setting.Message.RepairHand.no-money"));
                            }
                        } else {
                            $sender->sendMessage($this->getConfigData()->getNested("Setting.Message.RepairHand.invalid-item"));
                        }
                        
                        $sender->sendMessage($this->getConfigData()->getNested("Setting.Message.Rename.completed-repairhand"));
                        break;
                    case "rename":
                        if (count($args) < 2) {
                            $myMoney = $this->getEconomyAPI()->myMoney($sender);
                            $item = $sender->getInventory()->getItemInHand();
                            $price = $this->getConfigData()->getNested("Setting.Price.rename");
                            if ($item instanceof Tool || $item instanceof Armor) {
                                if ($myMoney > $price) {
                                    $item->setCustomName($args[1]);
                                } else {
                                    $sender->sendMessage($this->getConfigData()->getNested("Setting.Message.Rename.no-money"));
                                }
                            } else {
                                $sender->sendMessage($this->getConfigData()->getNested("Setting.Message.Rename.invalid-item"));
                            }
                        } else {
                            $sender->sendMessage($this->getConfigData()->getNested("Setting.Message.Rename.no-name"));
                        }
                        
                        $sender->sendMessage($this->getConfigData()->getNested("Setting.Message.Rename.completed-rename"));
                        break;
                    case "lore":
                        if (count($args) < 2) {
                            $myMoney = $this->getEconomyAPI()->myMoney($sender);
                            $item = $sender->getInventory()->getItemInHand();
                            $price = $this->getConfigData()->getNested("Setting.Price.lore");
                            if ($item instanceof Tool || $item instanceof Armor) {
                                if ($myMoney > $price) {
                                    $item->setLore(str_replace("{LINE}", "\n", TextFormat::colorize($args[1])));
                                } else {
                                    $sender->sendMessage($this->getConfigData()->getNested("Setting.Message.Lore.no-money"));
                                }
                            } else {
                                $sender->sendMessage($this->getConfigData()->getNested("Setting.Message.Lore.invalid-item"));
                            }
                        } else {
                            $sender->sendMessage($this->getConfigData()->getNested("Setting.Message.Lore.no-name"));
                        }
                        
                        $sender->sendMessage($this->getConfigData()->getNested("Setting.Message.Rename.completed-lore"));
                        break;
                }
            }
        }
        
        return true;
    }
    
    public function getEconomyAPI(): EconomyAPI {
        return $this->eco;
    }
    
    public function getConfigData(): Config {
        return $this->configData;
    }
    
    public function isItem(Item $item): bool{
        return $item instanceof Durable || $item instanceof Tool || $item instanceof Armor;
    }
}