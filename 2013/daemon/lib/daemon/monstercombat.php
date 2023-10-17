<?php
//@author Krzysztof Sikorski
class Daemon_MonsterCombat
{
	private $dbClient = null;
	private $characterData = null;
	private $combatLog;
	const WINNER_ACTOR = 'a';
	const WINNER_MONSTER = 'm';


	public function attachCharacterData(Daemon_DbObject_CharacterData $characterData)
	{
		$this->characterData = $characterData;
	}


	public function attachDbClient(Daemon_DbClient $dbClient)
	{
		$this->dbClient = $dbClient;
	}


	public function getCombatLog()
	{
		return $this->combatLog;
	}


	public function execute(Daemon_View $view, $monsterId, $fromEvent = false)
	{
		$char = $this->characterData;
		$winner = null;
		$winnerXp = 0;
		$winnerGold = null;
		$winnerLevel = null;
		$winnerDrop = null;
		$winnerMission = null;
		//load monster
		$monster = new Daemon_DbObject_Monster();
		$monster->attachDbClient($this->dbClient);
		$monster->get(array('monster_id' => $monsterId));
		//execute combat
		$winner = $this->runCombat($monster);
		//check for winner, modify character
		if(self::WINNER_ACTOR == $winner)
		{
			//experience
			$winnerXp = $monster->level;
			$this->characterData->xp_free += $winnerXp;
			//gold
			$winnerGold = $monster->gold;
			$this->characterData->gold_purse += $winnerGold;
			//level
			if($monster->level > $this->characterData->level)
			{
				$winnerLevel = $monster->level;
				$this->characterData->level = $winnerLevel;
			}
			else $winnerLevel = null;
			//statistics
			if(($monster->class >=1) && ($monster->class<=4))
			{
				$colname = "kills_mob{$monster->class}";
				$sql = "UPDATE character_statistics SET $colname=$colname+1 WHERE character_id=:id";
				$this->dbClient->query($sql, array('id' => $this->characterData->character_id));
			}
			//drop
			$winnerDrop = $this->rollDrop($monster);
			//title
			if($monster->title_id)
			{
				$sql = "INSERT IGNORE INTO character_titles(character_id, title_id) VALUES(:charId, :titleId)";
				$params = array(
					'charId' => $this->characterData->character_id,
					'titleId' => $monster->title_id);
				$this->dbClient->query($sql, $params);
			}
			//mission
			$mission = $this->characterData->getLastMission('active');
			if(('monster' == $mission['type']) && ($monster->monster_id == $mission['params']))
			{
				$sql = "UPDATE character_missions SET progress='completed'
					WHERE character_id=:cid AND rollover_id=:rid";
				$params = array('cid' => $this->characterData->character_id, 'rid' => $mission['rollover_id']);
				$this->dbClient->query($sql, $params);
				$winnerMission = true;
			}
		}
		elseif(self::WINNER_MONSTER == $winner)
			$this->characterData->setDeath(true);
		//update character
		$this->characterData->setLocationEvent(array());
		$this->characterData->put();
		//generate report
		$view->combatLog = $this->combatLog;
		$view->fromEvent = $fromEvent;
		$view->monsterName = $monster->name;
		$view->winner = $winner;
		$view->winnerXp = $winnerXp;
		$view->winnerGold = $winnerGold;
		$view->winnerLevel = $winnerLevel;
		$view->winnerDrop = $winnerDrop;
		$view->winnerMission = $winnerMission;
		ob_start();
		$view->display('monstercombat.xml');
		$this->combatLog = ob_get_clean();
	}


	//rolls for monster drops, return array of item names
	private function rollDrop(Daemon_DbObject_Monster $monster)
	{
		$itemId = null;
		$itemName = null;
		//check if there was any drop
		if($monster->chance2)
		{
			$d256 = mt_rand(0, 255);
			$chance = 256 * $monster->chance1 / $monster->chance2;
			$itemId = ($d256 < $chance);
		}
		if($itemId)
		{
			$itemId = null;
			//read drops
			$sql = "SELECT item_id, chance, name FROM monster_drops JOIN items USING(item_id) WHERE monster_id=:id";
			$drops = $this->dbClient->selectAll($sql, array('id' => $monster->monster_id));
			//roll drop
			$chanceSum = 0;
			foreach($drops as $row)
				$chanceSum += $row['chance'];
			$d256 = mt_rand(0, 255);
			foreach($drops as $row)
			{
				$chance = 256 * $row['chance'] / $chanceSum;
				if($d256 < $chance)
				{
					$itemId = $row['item_id'];
					$itemName = $row['name'];
					break;
				}
				$d256 -= $chance;
			}
			//give drop
			if($itemId)
			{
				$sql = "INSERT INTO inventory(character_id, item_id) VALUES (:charId, :itemId)";
				$params = array('charId' => $this->characterData->character_id, 'itemId' => $itemId);
				$this->dbClient->query($sql, $params);
			}
		}
		return $itemName;
	}


	//executes combat, returns winner flag
	private function runCombat(Daemon_DbObject_Monster $monster)
	{
		$combat = new Daemon_Combat();
		$logger = new Daemon_Combat_Log();
		$combat->attachLogger($logger);
		//prepare units
		$characterUnit = $this->characterData->getCombatUnit(true);
		$monsterUnit = $monster->getCombatUnit(true);
		$monsterUnit->health = $monsterUnit->health_max;
		//add units
		$combat->addUnit('a', $characterUnit, true);
		$combat->addUnit('b', $monsterUnit, false);
		//execute combat
		$combat->execute();
		$this->combatLog = (string) $logger;
		//update character
		$characterUnit->put();
		$this->characterData->health = floor($characterUnit->health);
		//check winner
		if($this->characterData->health < 1)
			return self::WINNER_MONSTER;
		if($monsterUnit->health < 1)
			return self::WINNER_ACTOR;
		return null;
	}
}
