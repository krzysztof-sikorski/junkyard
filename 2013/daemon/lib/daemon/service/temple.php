<?php
//@author Krzysztof Sikorski
class Daemon_Service_Temple extends Daemon_Service
{
	private $factionId;
	private $xpForMission;
	private $xpForRegen;


	public function execute($params)
	{
		$this->setParams();
		//run commands
		$this->isCommand = $this->runCommands($params);
		//generate output
		ob_start();
		$this->view->characterData = $this->characterData;
		$this->view->bankEnabled = $this->bankEnabled;
		$this->view->itemsToBind = $this->getItems(false, true);
		$this->view->itemsToOffer = $this->getItems(true, false);
		$this->view->lastMission = $this->characterData->getLastMission('completed');
		$this->view->display('service/temple.xml');
		$this->eventLog = ob_get_clean();
	}


	private function runCommands($params)
	{
		//bind item
		if(isset($params['bind']))
		{
			$this->bindItem($params['bind']);
			return true;
		}
		//pray at altar
		if(isset($params['pray']))
		{
			$inventoryId = isset($params['offer']) ? $params['offer'] : null;
			$this->pray($params['pray'], $inventoryId);
			return true;
		}
		//give up mission
		if(isset($params['giveUp']))
		{
			$this->removeMission();
			return true;
		}
		//check mission
		$this->checkMission();
		return false;
	}


	public function bindItem($inventoryId)
	{
		//get item data
		$sql = "SELECT i.name, i.value FROM inventory inv JOIN items i USING(item_id)
			WHERE inventory_id=:id AND character_id=:charId AND NOT FIND_IN_SET('bound', inv.flags)";
		$params = array('id' => $inventoryId, 'charId' => $this->characterData->character_id);
		$item = $this->dbClient->selectRow($sql, $params);
		if(!$item)
		{
			Daemon_MsgQueue::add('Wybrany przedmiot nie istnieje albo już jest przypisany.');
			return false;
		}
		//check character gold
		if(!$this->characterData->payGold($item['value'], $this->bankEnabled))
			return false;
		//bind item
		$sql = "UPDATE inventory SET flags = CONCAT(',bound,', flags) WHERE inventory_id=:id";
		$params = array('id' => $inventoryId);
		$this->dbClient->query($sql, $params);
	}


	//check if the mission is completed
	public function checkMission()
	{
		$inventoryId = null;
		$char = $this->characterData;
		$mission = $char->getLastMission('completed');
		//check conditions
		if(!$mission)
			return false;
		if('completed' != $mission['progress'])
			return false;
		if($this->serviceData['service_id'] != $mission['service_id'])
			return false;
		//give xp & gold
		$rewardXp = ceil(sqrt($char->xp_used));
		$rewardGold = mt_rand(10 * $rewardXp, 15 * $rewardXp);
		$char->xp_free += $rewardXp;
		if($this->bankEnabled)
			$char->gold_bank += $rewardGold;
		else $char->gold_purse += $rewardGold;
		$message = "Misja wykonana. Doświadczenie +$rewardXp, złoto +$rewardGold.";
		//give skill
		$skillNames = Daemon_Dictionary::$characterSkills;
		foreach(array_keys($skillNames) as $key)
		{
			$name = "s_$key";
			if($char->$name)
				unset($skillNames[$key]);
		}
		if(isset($skillNames['preg']) && ($char->xp_used >= $this->xpForRegen))
			$skill = 'preg';
		else //random other skill
		{
			unset($skillNames['preg']);
			$skill = array_rand($skillNames);
		}
		if($skill)
		{
			$skillName = $skillNames[$skill];
			$message .= " Poznajesz nową umiejętność: $skillName.";
			$name = "s_$skill";
			$char->$name = 1;
		}
		//join faction or raise reputation
		if($this->serviceData['faction_id'])
			$char->improveReputation($this->serviceData['faction_id'], mt_rand(4,6));
		//save character & mission
		$char->put();
		$sql = "UPDATE character_statistics SET missions=missions+1 WHERE character_id=:id";
		$this->dbClient->query($sql, array('id' => $char->character_id));
		$sql = "UPDATE character_missions SET progress='rewarded'
			WHERE character_id=:cid AND rollover_id=:rid";
		$params = array('cid' => $char->character_id, 'rid' => $mission['rollover_id']);
		$this->dbClient->query($sql, $params);
		Daemon_MsgQueue::add($message);
	}


	//returns a list of unbound items
	public function getItems($withoutEquipped = false, $withoutBound = false)
	{
		$result = array();
		$cond = "character_id=:id";
		if($withoutEquipped)
			$cond .= " AND inv.equipped IS NULL";
		if($withoutBound)
			$cond .= " AND NOT FIND_IN_SET('bound', inv.flags)";
		if(!$this->bankEnabled)
			$cond .= " AND status!='storage'";
		$sql = "SELECT inv.*, i.name, i.value FROM inventory inv JOIN items i USING(item_id)
			WHERE $cond ORDER BY i.name, inv.inventory_id";
		$params = array('id' => $this->characterData->character_id);
		foreach($this->dbClient->selectAll($sql, $params) as $row)
		{
			$flags = $row['flags'] ? array_fill_keys(explode(',', $row['flags']), true) : array();
			$status = $row['status'];
			if(('inventory' == $status) && $row['equipped'])
				$status = 'equipment';
			$result[$status][$row['inventory_id']] = array(
				'name' => $row['name'], 'value' => $row['value'], 'flags' => $flags);
		}
		return $result;
	}


	//gives mission if its allowed
	private function giveMission()
	{
		$char = $this->characterData;
		$lastMission = $char->getLastMission('rewarded');
		//check character's xp
		if($char->xp_used < $this->xpForMission)
		{
			switch($char->_gender)
			{
				case 'm': $weak = 'słaby'; break;
				case 'k': $weak = 'słaba'; break;
				default: $weak = 'słabe';
			}
			Daemon_MsgQueue::add("Słyszysz głos z nieba: \"Twa gorliwość jest godna pochwały, lecz jesteś jeszcze za $weak - wróć gdy osiągniesz $this->xpForMission doświadczenia.\"");
			return false;
		}
		//check last mission
		$sql = "SELECT MAX(rollover_id) FROM rollovers";
		$lastRollover = $this->dbClient->selectValue($sql);
		if (empty($lastRollover))
		{
			Daemon_MsgQueue::add('Słyszysz głos z nieba: "Twa gorliwość jest godna pochwały, lecz jeszcze za wcześnie na misje. Wróć później."');
			return false;
		}
		if($lastRollover && $lastMission && ($lastRollover <= $lastMission['rollover_id']))
		{
			switch($char->_gender)
			{
				case 'm': $x = 'wykonałeś'; break;
				case 'k': $x = 'wykonałaś'; break;
				default: $x = 'wykonałeś';
			}
			Daemon_MsgQueue::add("Słyszysz głos z nieba: \"Twa gorliwość jest godna pochwały, lecz $x już dzisiaj misję. Wróć później.");
			return false;
		}
		//get character's region
		$sql = "SELECT region_id FROM locations WHERE location_id=:id";
		$regionId = $this->dbClient->selectValue($sql, array('id' => $char->location_id));
		//find monster near character's level & location
		$cols = "m.monster_id, m.name";
		$tables = "monsters m JOIN location_monsters lm USING(monster_id) JOIN locations l USING(location_id)";
		$params = array('regionId' => $regionId, 'level' => $char->level);
		$sql = "SELECT $cols FROM $tables
			WHERE l.region_id=:regionId AND m.level>=:level AND lm.chance > 0
			ORDER BY level ASC, RAND() LIMIT 1";
		$monster = $this->dbClient->selectRow($sql, $params);
		if(!$monster)
		{
			$sql = "SELECT $cols FROM $tables
				WHERE l.region_id=:regionId AND m.level<:level AND lm.chance > 0
				ORDER BY level DESC, RAND() LIMIT 1";
			$monster = $this->dbClient->selectRow($sql, $params);
		}
		if(!$monster)
			return false;
		//get random drop from that monster
		$sql = "SELECT i.item_id, i.name, md.chance*RAND() AS ord
			FROM items i JOIN monster_drops md USING(item_id)
			WHERE md.monster_id=:id AND md.chance > 0 ORDER BY ord LIMIT 1";
		$params = array('id' => $monster['monster_id']);
		$item = $this->dbClient->selectRow($sql, $params);
		//choose mission type
		if($item && (mt_rand(0,255) < 128))
		{
			$missionType = 'item';
			$missionParams = $item['item_id'];
			$message = "Przynieś mi przedmiot - $item[name] - a twój wysiłek zostanie nagrodzony.";
		}
		else
		{
			$missionType = 'monster';
			$missionParams = $monster['monster_id'];
			$message = "Pokonaj potwora - $monster[name] - a twój wysiłek zostanie nagrodzony.";
		}
		$sql = "INSERT INTO character_missions(character_id, rollover_id, service_id, type, params)
			VALUES (:cid, :rid, :sid, :type, :params)";
		$params = array('cid' => $char->character_id, 'rid' => $lastRollover,
			'sid' => $this->serviceData['service_id'], 'type' => $missionType, 'params' => $missionParams);
		$this->dbClient->query($sql, $params);
		Daemon_MsgQueue::add("Słyszysz głos z nieba: \"$message\"");
		return true;
	}


	//pray at altar, offering gold
	public function pray($gold, $inventoryId)
	{
		$char = $this->characterData;
		$mission = $char->getLastMission('active');
		$gold = ceil(abs($gold));
		//check character gold
		if(!$char->payGold($gold, $this->bankEnabled))
			return false;
		//check item for value and mission
		$cond = "inv.character_id=:charId AND inv.inventory_id=:inventoryId";
		if(!$this->bankEnabled)
			$cond .= " AND inv.status!='storage'";
		$sql = "SELECT inv.item_id, i.value FROM inventory inv JOIN items i USING(item_id) WHERE $cond";
		$params = array('charId' => $char->character_id, 'inventoryId' => $inventoryId);
		$item = $this->dbClient->selectRow($sql, $params);
		if($item)
		{
			$gold += $item['value'];
			//check for mission target
			if(($this->serviceData['service_id'] == $mission['service_id']) && ('item' == $mission['type'])
				&& ($item['item_id'] == $mission['params']) && ('completed' != $mission['progress']))
			{
				$mission['progress'] = 1;
				$sql = "UPDATE character_missions SET progress='completed' WHERE character_id=:cid AND rollover_id=:rid";
				$params = array('cid' => $char->character_id, 'rid' => $mission['rollover_id']);
				$this->dbClient->query($sql, $params);
			}
			//remove item
			$sql = "DELETE FROM inventory WHERE inventory_id=:id";
			$this->dbClient->query($sql, array('id' => $inventoryId));
			$char->resetCombatStats();
		}
		//check for reaction
		if(!$mission['progress'])
		{
			$wealth = $gold + $char->gold_purse + $char->gold_bank;
			if($wealth)
				$chance = round(2560 * $gold / $wealth);
			else $chance = 0;
			$d256 = mt_rand(0, 255);
			if($chance <= $d256)
			{
				Daemon_msgQueue::add('Twa ofiara nie wywołała żadnej reakcji.');
				return false;
			}
		}
		//heal character & give mission
		$char->health = $char->health_max;
		$char->mana = $char->mana_max;
		Daemon_MsgQueue::add('Stwórca przyjął ofiarę. Twoje rany zostały uleczone a siły odnowione.');
		if($mission['progress'])
			$this->checkMission();
		else $this->giveMission();
		//save character
		$this->characterData->put();
		return true;
	}


	//removes mission
	public function removeMission()
	{
		$sql = "DELETE FROM character_missions WHERE character_id=:id AND progress < 'completed'";
		$this->dbClient->query($sql, array('id' => $this->characterData->character_id));
	}


	public function setParams()
	{
		$dbCfg = new Daemon_DbConfig($this->dbClient);
		$this->xpForMission = max(0, (int) $dbCfg->templeXpForMission);
		$this->xpForRegen = max(0, (int) $dbCfg->templeXpForRegen);
	}
}
