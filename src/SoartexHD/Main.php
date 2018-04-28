<?php

namespace SoartexHD;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\scheduler\PluginTask;
use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\utils\Config;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\entity\Entity;
use pocketmine\event\entity\ExplosionPrimeEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\entity\Effect;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\level\sound\AnvilUseSound;
use pocketmine\level\sound\EndermanTeleportSound;
use pocketmine\utils\Textformat as Color;

class Main extends PluginBase implements Listener {

    public $prefix = "";
    public $hideall = [];

    public function onEnable () {
		
		$prefix = new Config($this->getDataFolder() . "prefix.yml", Config::YAML);
            if(empty($prefix->get("Prefix"))) {
                $prefix->set("Prefix", "§7[§6§lLOBBY§r§7]");
			}
			$prefix->save();

        $this->saveResource("config.yml");
        @mkdir($this->getDataFolder());
        $this->prefix = $prefix->get("Prefix");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getServer()->getLogger()->info("§4--------------------------------");
        $this->getServer()->getLogger()->info("§7[§6§lLOBBY§r§7] §awurde Aktiviert");
        $this->getServer()->getLogger()->info("      §5§lPlugin by PrinxIsLeqit");
        $this->getServer()->getLogger()->info("§4--------------------------------");
		
		$config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
            if(empty($config->get("JoinBroadcast"))) {
                $config->set("JoinBroadcast1", "§7=======================");
                $config->set("LEER", "");
                $config->set("JoinBroadcast2", " §8» §6Willkommen auf unserem Server");
                $config->set("JoinBroadcast3", " §8» §fWEBSITE§7 × §5YOURWEBSITE.NET");
                $config->set("JoinBroadcast4", " §8» §fDISCORD§7 × §5YOURDISCORD");
                $config->set("LEER2", "");
                $config->set("JoinBroadcast5", "§7=======================");
                $config->set("BlockBreakMessage", " §cDu kannst hier nicht abbauen");
                $config->set("Hub/Lobby", " §c Willkommen in der Lobby");
                $config->set("JoinTitle", " §7[§a»§7] §aWllkommen");
                $config->set("Prefix", "§7[§6§lLOBBY§r§7]");
				$config->set("Chat", " §7Du musst den Rang §6Premium§7 besitzen um schreiben zu koennen!");
        }
        $config->save();

        $info = new Config($this->getDataFolder() . "info.yml", Config::YAML);
        if(empty($info->get("infoline1"))){
            $info->set("infoline1", "§7===§7[§f§lSERVERNAME.NET§r§7]===");
            $info->set("infoline2", "§7» §3bei Weiteren Fragen melde dich im Discord");
            $info->set("infoline3", "§7» §6Discord IP");
            $info->set("infoline4", "§7» §6Ts IP");
            $info->set("infoline5", "§7=================");
            $info->set("Popup", "» §6Vielen Dank");
        }
        $info->save();

        $LobbyTitle = new Config($this->getDataFolder() . "Title.yml", Config::YAML);
        if(empty($LobbyTitle->get("LobbySendigBackTitle"))){
            $LobbyTitle->set("LobbySendigBackTitle", "§7» §6Lobby");
        }
        $LobbyTitle->save();


    }
    public function onJoin(PlayerJoinEvent $ev) {
		
		$config = new Config($this->getDataFolder() . "config.yml", Config::YAML);

        $player = $ev->getPlayer();
        $player->getInventory()->clearAll();
        $ev->setJoinMessage("");
        $player->setFood(20);
        $player->setHealth(20);
        $player->setGamemode(0);
        $player->getlevel()->addSound(new AnvilUseSound($player));
        $player->addTitle("§7[§a»§7] §aWllkommen", "");
        $player->sendPopup("§7× §6Willkommen " . Color::WHITE . $player->getDisplayName() . Color::DARK_GRAY . " ×");
        $player->teleport($this->getServer()->getDefaultLevel()->getSafeSpawn());
        $player->sendMessage($config->get("JoinBroadcast1"));
        $player->sendMessage($config->get("LEER"));
        $player->sendMessage($config->get("JoinBroadcast2"));
        $player->sendMessage($config->get("JoinBroadcast3"));
        $player->sendMessage($config->get("JoinBroadcast4"));
        $player->sendMessage($config->get("LEER2"));
        $player->sendMessage($config->get("JoinBroadcast5"));

        $player->getInventory()->setSize(9);
        $player->getInventory()->setItem(4, Item::get(339)->setCustomName("§7× §aInfo §7×"));
        $player->getInventory()->setItem(0, Item::get(345)->setCustomName("§7× §4Teleporter §7×"));
        $player->getInventory()->setItem(8, Item::get(54)->setCustomName("§7× §3Extras §7×"));
        if($player->hasPermission("lobby.yt")){
            $player->getInventory()->setItem(7, Item::get(288)->setCustomName("§7× §fFly §7×"));
        }else{
            $player->getInventory()->setItem(7, Item::get(152)->setCustomName("§7× §fFly §7[§6Premium§7] §7×"));
        }
        $player->getInventory()->setItem(1, Item::get(369)->setCustomName("§7× §eSpieler verstecken §8[§aSICHBAR§8] §7×"));

    }

    public function onBreak(BlockBreakEvent $ev) {
		
		$config = new Config($this->getDataFolder() . "config.yml", Config::YAML);

        $player = $ev->getPlayer();
        $ev->setCancelled(true);
        $player->sendMessage($this->prefix . $config->get("BlockBreakMessage"));

    }

    public function onQuit(PlayerQuitEvent $ev) {

        $player = $ev->getPlayer();
        $name = $player->getName();

        $ev->setQuitMessage("");
        $player->sendPopup("§7[§c-§7] ". Color::DARK_GRAY . $name);
    }

    public function onPlace(BlockPlaceEvent $ev) {

        $player = $ev->getPlayer();
        $ev->setCancelled(true);

    }

    public function Hunger(PlayerExhaustEvent $ev) {

        $ev->setCancelled(true);

    }

    public function ItemMove(PlayerDropItemEvent $ev){

        $ev->setCancelled(true);
    }

    public function onConsume(PlayerItemConsumeEvent $ev){

        $ev->setCancelled(true);
    }

    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool {

        switch($cmd->getName()){

            case "hub";

                $LobbyTitle = new Config($this->getDataFolder() . "Title.yml", Config::YAML);
				$config = new Config($this->getDataFolder() . "config.yml", Config::YAML);

                $sender->sendMessage($this->prefix . $config->get("Hub/Lobby"));
                $sender->teleport($this->getServer()->getDefaultLevel()->getSafeSpawn());
                $sender->addTitle($LobbyTitle->get("LobbySendigBackTitle"));

            case "lobby";

                $LobbyTitle = new Config($this->getDataFolder() . "Title.yml", Config::YAML);
				$config = new Config($this->getDataFolder() . "config.yml", Config::YAML);

                $sender->sendMessage($this->prefix . $config->get("Hub/Lobby"));
                $sender->teleport($this->getServer()->getDefaultLevel()->getSafeSpawn());
                $sender->addTitle($LobbyTitle->get("LobbySendigBackTitle"));
                return true;
        }
    }

    public function onDamage(EntityDamageEvent $ev){

        if($ev->getCause() === EntityDamageEvent::CAUSE_FALL){
            $ev->setCancelled(true);
        }

    }
	
	public function onChat(PlayerChatEvent $ev){
        
        $p = $ev->getPlayer();
		$config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        		
	    if($p->hasPermission("lobby.chat")){
		$ev->setCancelled(false);    
	    }else{
	    	$p->sendMessage($this->prefix . $config->get("Chat"));
	    	$ev->setCancelled(true);
	    }
		
	}

    public function onInteract(PlayerInteractEvent $ev){

        $player = $ev->getPlayer();
        $item = $ev->getItem();
        $info = new Config($this->getDataFolder() . "info.yml", Config::YAML);
		$config = new Config($this->getDataFolder() . "config.yml", Config::YAML);

        if($item->getCustomName() == "§7× §aInfo §7×"){
            $player->sendMessage($info->get("infoline1"));
            $player->sendMessage($info->get("infoline2"));
            $player->sendMessage($info->get("infoline3"));
            $player->sendMessage($info->get("infoline4"));
            $player->sendMessage($info->get("infoline5"));
            $player->sendPopup($info->get("Popup"));

        }elseif($item->getCustomName() == "§7× §4Teleporter §7×"){

            $player->getInventory()->clearAll();
            $player->getInventory()->setSize(9);
            $player->getInventory()->setItem(0, Item::get(160)->setCustomName("§7-"));
            $player->getInventory()->setItem(1, Item::get(2)->setCustomName("§3SKYWARS"));
            $player->getInventory()->setItem(2, Item::get(160)->setCustomName("§7-"));
            $player->getInventory()->setItem(3, Item::get(283)->setCustomName("§6SURVIVALGAMES"));
            $player->getInventory()->setItem(4, Item::get(160)->setCustomName("§7-"));
            $player->getInventory()->setItem(5, Item::get(399)->setCustomName("§2§lSPAWN"));
            $player->getInventory()->setItem(8, Item::get(351, 1)->setCustomName("§4» §cZurück §4»"));

        }elseif($item->getCustomName() == "§7× §3Extras §7×"){

            $player->sendPopup("§8» §6Hier findest du tolle Extras & Effekte");
            $player->getlevel()->addSound(new AnvilUseSound($player));
			$player->removeAllEffects();
            $player->getInventory()->clearAll();
            if($player->hasPermission("lobby.yt")){
                $player->getInventory()->setItem(0, Item::get(377)->setCustomName("§6Effekte"));
                $player->getInventory()->setItem(2, Item::get(38)->setCustomName("§dBoots"));
                $player->getInventory()->setItem(8, Item::get(351, 1)->setCustomName("§4» §cZurück §4»"));
                $player->getInventory()->setItem(1, Item::get(160)->setCustomName("§7-"));
            }else {
                $player->getInventory()->setItem(8, Item::get(351, 1)->setCustomName("§4» §cZurück §4»"));
                $player->getInventory()->setItem(1, Item::get(160)->setCustomName("§7-"));
                $player->getInventory()->setItem(0, Item::get(377)->setCustomName("§6Effekte §7[§6Premium§7]"));
                $player->getInventory()->setItem(2, Item::get(38)->setCustomName("§dBoots §7[§6Premium§7]"));
            }

        }elseif($item->getCustomName() == "§6Effekte"){

            $player->sendPopup("§8» §6Hier findest du tolle Extras & Effekte");
            $player->getInventory()->clearAll();
            $player->getInventory()->setSize(9);
            $player->getInventory()->setItem(0, Item::get(260)->setCustomName("§8§l»§r §aJumpboost"));
            $player->getInventory()->setItem(1, Item::get(160)->setCustomName(""));
            $player->getInventory()->setItem(2, Item::get(264)->setCustomName("§8§l»§r §3Speedboost"));
            $player->getInventory()->setItem(3, Item::get(160)->setCustomName(""));
            $player->getInventory()->setItem(4, Item::get(264)->setCustomName("§8§l»§r §fGhost"));
			$player->getInventory()->setItem(6, Item::get(32)->setCustomName("§8» §c§lausschalten"));
            $player->getInventory()->setItem(8, Item::get(351, 1)->setCustomName("§4» §cZurück [§fSeite 1.] §4»"));

        }elseif($item->getCustomName() == "§3SKYWARS"){

            $player->sendMessage("");
            $player->sendMessage($this-> prefix . Color::RED . " §7Du wurdest zu §3Skywars §7Teleportiert");
            $player->teleport(new Vector3(212, 71, 138));
            $player->getlevel()->addSound(new EndermanTeleportSound($player));
            $player->getInventory()->clearAll();
            $player->getInventory()->setSize(9);
            $player->getInventory()->setItem(4, Item::get(339)->setCustomName("§7× §aInfo §7×"));
            $player->getInventory()->setItem(0, Item::get(345)->setCustomName("§7× §4Teleporter §7×"));
            $player->getInventory()->setItem(8, Item::get(54)->setCustomName("§7× §3Extras §7×"));
            if($player->hasPermission("lobby.yt")){
                $player->getInventory()->setItem(7, Item::get(288)->setCustomName("§7× §fFly §7×"));
            }else{
                $player->getInventory()->setItem(7, Item::get(152)->setCustomName("§7× §fFly §7[§6Premium§7] §7×"));
            }
            $player->getInventory()->setItem(1, Item::get(369)->setCustomName("§7× §eSpieler verstecken §8[§aSICHBAR§8] §7×"));

        }elseif($item->getCustomName() == "§6SURVIVALGAMES"){

            $player->sendMessage("");
            $player->sendMessage($this-> prefix . Color::RED . " §7Du wurdest zu §6Survivalgames §7Teleportiert");
            $player->teleport(new Vector3(212, 71, 138));
            $player->getlevel()->addSound(new EndermanTeleportSound($player));
            $player->getInventory()->clearAll();
            $player->getInventory()->setSize(9);
            $player->getInventory()->setItem(4, Item::get(339)->setCustomName("§7× §aInfo §7×"));
            $player->getInventory()->setItem(0, Item::get(345)->setCustomName("§7× §4Teleporter §7×"));
            $player->getInventory()->setItem(8, Item::get(54)->setCustomName("§7× §3Extras §7×"));
            if($player->hasPermission("lobby.yt")){
                $player->getInventory()->setItem(7, Item::get(288)->setCustomName("§7× §fFly §7×"));
            }else{
                $player->getInventory()->setItem(7, Item::get(152)->setCustomName("§7× §fFly §7[§6Premium§7] §7×"));
            }
            $player->getInventory()->setItem(1, Item::get(369)->setCustomName("§7× §eSpieler verstecken §8[§aSICHBAR§8] §7×"));

        }elseif($item->getCustomName() == "§4» §cZurück §4»"){

            $player->getInventory()->clearAll();
			$player->getInventory()->setItem(4, Item::get(339)->setCustomName("§7× §aInfo §7×"));
            $player->getInventory()->setItem(0, Item::get(345)->setCustomName("§7× §4Teleporter §7×"));
            $player->getInventory()->setItem(8, Item::get(54)->setCustomName("§7× §3Extras §7×"));
            if($player->hasPermission("lobby.yt")){
                $player->getInventory()->setItem(7, Item::get(288)->setCustomName("§7× §fFly §7×"));
            }else{
                $player->getInventory()->setItem(7, Item::get(152)->setCustomName("§7× §fFly §7[§6Premium§7] §7×"));
            }
            $player->getInventory()->setItem(1, Item::get(369)->setCustomName("§7× §eSpieler verstecken §8[§aSICHBAR§8] §7×"));

        }elseif($item->getCustomName() == "§8§l»§r §aJumpboost") {

            $player->removeAllEffects();
            $effect = Effect::getEffect(Effect::JUMP);
            $effect->setAmplifier(3);
            $effect->setDuration(500);
            $player->addEffect($effect);
            $player->sendMessage($this->prefix . Color::WHITE . " §7Du hast den Effekt §a§lJUMPBOOST§r §7ausgewält");
            $player->sendPopup("§8§l»§r §aJumpboost§7: §cAktiviert");
			$player->getInventory()->clearAll();
			$player->getInventory()->setSize(9);
            $player->getInventory()->setItem(4, Item::get(339)->setCustomName("§7× §aInfo §7×"));
            $player->getInventory()->setItem(0, Item::get(345)->setCustomName("§7× §4Teleporter §7×"));
            $player->getInventory()->setItem(8, Item::get(54)->setCustomName("§7× §3Extras §7×"));
            if($player->hasPermission("lobby.yt")){
            $player->getInventory()->setItem(7, Item::get(288)->setCustomName("§7× §fFly §7×"));
            }else{
            $player->getInventory()->setItem(7, Item::get(152)->setCustomName("§7× §fFly §7[§6Premium§7] §7×"));
            }
            $player->getInventory()->setItem(1, Item::get(369)->setCustomName("§7× §eSpieler verstecken §8[§aSICHBAR§8] §7×"));

        }elseif($item->getCustomName() == "§8§l»§r §3Speedboost") {

            $player->removeAllEffects();
            $effect = Effect::getEffect(Effect::SPEED);
            $effect->setAmplifier(3);
            $effect->setDuration(500);
            $player->addEffect($effect);
            $player->sendMessage($this->prefix . Color::WHITE . " §7Du hast den Effekt §3§lSPEEDBOOST§r §7ausgewält");
            $player->sendPopup("§8§l»§r §3Speedboost§7: §cAktiviert");
			$player->getInventory()->clearAll();
			$player->getInventory()->setSize(9);
            $player->getInventory()->setItem(4, Item::get(339)->setCustomName("§7× §aInfo §7×"));
            $player->getInventory()->setItem(0, Item::get(345)->setCustomName("§7× §4Teleporter §7×"));
            $player->getInventory()->setItem(8, Item::get(54)->setCustomName("§7× §3Extras §7×"));
            if($player->hasPermission("lobby.yt")){
            $player->getInventory()->setItem(7, Item::get(288)->setCustomName("§7× §fFly §7×"));
            }else{
            $player->getInventory()->setItem(7, Item::get(152)->setCustomName("§7× §fFly §7[§6Premium§7] §7×"));
            }
            $player->getInventory()->setItem(1, Item::get(369)->setCustomName("§7× §eSpieler verstecken §8[§aSICHBAR§8] §7×"));

        }elseif($item->getCustomName() == "§8§l»§r §fGhost"){

            $player->removeAllEffects();
            $effect = Effect::getEffect(Effect::INVISIBILITY);
            $effect->setAmplifier(3);
            $effect->setDuration(500);
            $player->addEffect($effect);
            $player->sendMessage($this->prefix . Color::WHITE . " §7Du hast den Effekt §f§lGhost§r §7ausgewält");
            $player->sendPopup("§8§l»§r §fGhost§7: §cAktiviert");
			$player->getInventory()->clearAll();
			$player->getInventory()->setSize(9);
            $player->getInventory()->setItem(4, Item::get(339)->setCustomName("§7× §aInfo §7×"));
            $player->getInventory()->setItem(0, Item::get(345)->setCustomName("§7× §4Teleporter §7×"));
            $player->getInventory()->setItem(8, Item::get(54)->setCustomName("§7× §3Extras §7×"));
            if($player->hasPermission("lobby.yt")){
            $player->getInventory()->setItem(7, Item::get(288)->setCustomName("§7× §fFly §7×"));
            }else{
            $player->getInventory()->setItem(7, Item::get(152)->setCustomName("§7× §fFly §7[§6Premium§7] §7×"));
            }
            $player->getInventory()->setItem(1, Item::get(369)->setCustomName("§7× §eSpieler verstecken §8[§aSICHBAR§8] §7×"));

        }elseif($item->getCustomName() == "§7× §fFly §7×"){


            $player->getInventory()->clearAll();
            $player->getInventory()->setSize(9);
            $player->getInventory()->setItem(0, Item::get(341)->setCustomName("§8§l»§r §aAKTIVIEREN"));
            $player->getInventory()->setItem(4, Item::get(376)->setCustomName("§8§l»§r §4DEAKTIVIEREN"));
            $player->getInventory()->setItem(8, Item::get(351, 1)->setCustomName("§4» §cZurück §4»"));

        }elseif($item->getCustomName() == "§8§l»§r §aAKTIVIEREN"){

            $player->setAllowFlight(true);
            $player->sendMessage($this->prefix . Color::WHITE . " §7Du hast §3§lFLY§r §7aktiviert.");
            $player->sendPopup("§8§l»§r §3FLY§7: §aAktiviert");

        }elseif($item->getCustomName() == "§8§l»§r §4DEAKTIVIEREN"){

            $player->setAllowFlight(false);
            $player->setHealth(20);
            $player->setFood(20);
            $player->sendMessage($this->prefix . Color::WHITE . " §7Du hast §3§lFLY§r §7deaktiviert.");
            $player->sendPopup("§8§l»§r §3FLY§7: §cDeaktiviert");

        }elseif($item->getCustomName() == "§7× §eSpieler verstecken §8[§aSICHBAR§8] §7×"){

            $player->getInventory()->setItem(1, Item::get(280)->setCustomName("§7× §eSpieler verstecken §8[§cUNSICHTBAR§8] §7×"));
            $this->hideall[] = $player;
            $player->sendMessage ($this->prefix . " §7Die Spieler sind jetzt §8[§c§lUNSICHTBAR§r§8]");

        }elseif($item->getCustomName() == "§7× §eSpieler verstecken §8[§cUNSICHTBAR§8] §7×"){

            unset($this->hideall[array_search($player, $this->hideall)]);
            foreach($this->getServer()->getOnlinePlayers() as $p){
                $player->showPlayer($p);
            }
            $player->getInventory()->setItem(1, Item::get(369)->setCustomName("§7× §eSpieler verstecken §8[§aSICHBAR§8] §7×"));
            $player->sendMessage ($this->prefix . " §7Die Spieler sind jetzt §8[§a§lSICHTBAR§r§8]");

        }elseif($item->getCustomName() == "§dBoots") {
			
            $player->getInventory()->clearAll();
			$player->getInventory()->setSize(9);
            $player->getInventory()->setItem(0, Item::get(309)->setCustomName("§7§lEISENSCHUHE"));
			$player->getInventory()->setItem(6, Item::get(32)->setCustomName("§8» §4§lausschalten"));
            $player->getInventory()->setItem(8, Item::get(351, 1)->setCustomName("§4» §cZurück §4»"));

        }elseif($item->getCustomName() == "§7× §fFly §7[§6Premium§7] §7×"){

            $player->sendMessage($this->prefix . " §7Dieses Funktion duerfen nur §6Premium§7 Spieler verwenden");

        }elseif($item->getCustomName() == "§2§lSPAWN"){

            $player->sendMessage($this->prefix . $config->get("Hub/Lobby"));
            $player->teleport($this->getServer()->getDefaultLevel()->getSafeSpawn());
            $player->addTitle("§7» §6Lobby", "");
            $player->getInventory()->clearAll();
            $player->getInventory()->setSize(9);
            $player->getInventory()->setItem(4, Item::get(339)->setCustomName("§7× §aInfo §7×"));
            $player->getInventory()->setItem(0, Item::get(345)->setCustomName("§7× §4Teleporter §7×"));
            $player->getInventory()->setItem(8, Item::get(54)->setCustomName("§7× §3Extras §7×"));
            if($player->hasPermission("lobby.yt")){
                $player->getInventory()->setItem(7, Item::get(288)->setCustomName("§7× §fFly §7×"));
            }else{
                $player->getInventory()->setItem(7, Item::get(152)->setCustomName("§7× §fFly §7[§6Premium§7] §7×"));
            }
            $player->getInventory()->setItem(1, Item::get(369)->setCustomName("§7× §eSpieler verstecken §8[§aSICHBAR§8] §7×"));

        }elseif($item->getCustomName() == "§6Effekte §7[§6Premium§7]"){

            $player->sendMessage($this->prefix . " §7Dieses Funktion duerfen nur §6Premium§7 Spieler verwenden");

        }elseif($item->getCustomName() == "§dBoots §7[§6Premium§7]"){

            $player->sendMessage($this->prefix . " §7Dieses Funktion duerfen nur §6Premium§7 Spieler verwenden");
			
        }elseif($item->getCustomName() == "§7§lEISENSCHUHE"){
			
			$player->getInventory()->clearAll();
			$player->getInventory()->setBoots(Item::get(Item::IRON_BOOTS));
			$player->getInventory()->setSize(9);
            $player->getInventory()->setItem(4, Item::get(339)->setCustomName("§7× §aInfo §7×"));
            $player->getInventory()->setItem(0, Item::get(345)->setCustomName("§7× §4Teleporter §7×"));
            $player->getInventory()->setItem(8, Item::get(54)->setCustomName("§7× §3Extras §7×"));
            if($player->hasPermission("lobby.yt")){
            $player->getInventory()->setItem(7, Item::get(288)->setCustomName("§7× §fFly §7×"));
            }else{
            $player->getInventory()->setItem(7, Item::get(152)->setCustomName("§7× §fFly §7[§6Premium§7] §7×"));
            }
            $player->getInventory()->setItem(1, Item::get(369)->setCustomName("§7× §eSpieler verstecken §8[§aSICHBAR§8] §7×"));
			$player->sendMessage($this->prefix . " §7Du hast die §a§lEISENSCHUHE§r §7angezogen");
			
		}elseif($item->getCustomName() == "§8» §c§lausschalten"){
			
			$player->removeAllEffects();
			$player->sendMessage($this->prefix . " §7Du hast alle Effekte oder Boots §c§lDeaktiviert§r");
			
		}elseif($item->getCustomName() == "§8» §4§lausschalten"){
			
			$player->getInventory()->clearAll();
			$player->sendMessage($this->prefix . " §7Du hast alle Effekte oder Boots §c§lDeaktiviert§r");
			$player->getInventory()->setSize(9);
            $player->getInventory()->setItem(0, Item::get(309)->setCustomName("§7§lEISENSCHUHE"));
			$player->getInventory()->setItem(6, Item::get(32)->setCustomName("§8» §4§lausschalten"));
            $player->getInventory()->setItem(8, Item::get(351, 1)->setCustomName("§4» §cZurück §4»"));
			
		}elseif($item->getCustomName() == "§4» §cZurück §f[§fSeite 1.] §4»"){
			
			$player->getInventory()->clearAll();
			$player->getInventory()->setSize(9);
			if($player->hasPermission("lobby.yt")){
                $player->getInventory()->setItem(0, Item::get(377)->setCustomName("§6Effekte"));
                $player->getInventory()->setItem(2, Item::get(38)->setCustomName("§dBoots"));
                $player->getInventory()->setItem(8, Item::get(351, 1)->setCustomName("§4» §cZurück §4»"));
                $player->getInventory()->setItem(1, Item::get(160)->setCustomName("§7-"));
            }else {
                $player->getInventory()->setItem(8, Item::get(351, 1)->setCustomName("§4» §cZurück §4»"));
                $player->getInventory()->setItem(1, Item::get(160)->setCustomName("§7-"));
                $player->getInventory()->setItem(0, Item::get(377)->setCustomName("§6Effekte §7[§6Premium§7]"));
                $player->getInventory()->setItem(2, Item::get(38)->setCustomName("§dBoots §7[§6Premium§7]"));
            }
			
		}

    }

}
