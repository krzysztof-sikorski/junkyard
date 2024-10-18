<?php
//@author Krzysztof Sikorski
class Daemon_DbObject_Location extends Daemon_DbObject
{
	protected $_tableName = 'locations';
	protected $_index = array('location_id');
	public $location_id;
	public $name;
	public $type = 'normal';
	public $chance1 = 1, $chance2 = 1;
	public $region_id = null;
	public $faction_id = null;
	public $description = null;
	public $picture_url = null;
	private $_characterData = null;


	//spends one turn on hunting
	public function actionHunt()
	{
		//check turn costs
		if(!$this->_characterData->checkTurnCosts())
			return false;
		//update character data
		$this->_characterData->regen(false);
		//check for events
		$this->checkEvents(2);
		//save character data
		$this->_characterData->put();
		return true;
	}


	//spends one turn on resting
	public function actionRest()
	{
		//check turn costs
		if(!$this->_characterData->checkTurnCosts())
			return false;
		//update character data
		$this->_characterData->regen(true);
		//check for events
		$this->checkEvents(1);
		//save character data
		$this->_characterData->put();
		return true;
	}


	//spends one turn on training
	public function actionTrain()
	{
		//check turn costs
		if(!$this->_characterData->checkTurnCosts())
			return false;
		//update character data
		$this->_characterData->regen(false);
		$this->_characterData->xp_free += 1;
		//check for events
		$this->checkEvents(1);
		//save character data
		$this->_characterData->put();
		return true;
	}


	//moves character to specified location
	public function actionTravel($destinationId)
	{
		//read path data
		$sql = "SELECT * FROM location_paths WHERE location_id=:id AND destination_id=:destId";
		$params = array('id' => $this->location_id, 'destId' => $destinationId);
		$path = $this->_dbClient->selectRow($sql, $params);
		if(!$path)
		{
			Daemon_MsgQueue::add('Wybrana ścieżka jest niedostępna.');
			return false;
		}
		//check turn costs
		if(!$this->_characterData->checkTurnCosts($path['cost_gold'], $path['cost_mana']))
			return false;
		//update character data
		$this->_characterData->regen(false);
		$this->_characterData->location_id = $destinationId;
		//load destination
		$this->get(array('location_id' => $destinationId));
		if($this->region_id)
		{
			$sql = "INSERT IGNORE INTO character_regions (character_id, region_id) VALUES (:charId, :regionId)";
			$params = array('charId' => $this->_characterData->character_id, 'regionId' => $this->region_id);
			$this->_dbClient->query($sql, $params);
		}
		//check for events
		$this->checkEvents(1);
		//save character data
		$this->_characterData->put();
		return true;
	}


	public function attachCharacterData(Daemon_DbObject_CharacterData $characterData)
	{
		$this->_characterData = $characterData;
	}


	//checks for events in a location
	private function checkEvents($chanceMult)
	{
		$event = false;
		$eventParams = array();
		//check if there was any event
		if($this->chance2)
		{
			$d256 = mt_rand(0, 255);
			$failChance = 256 * (1 - $this->chance1 / $this->chance2);
			$chance = $chanceMult ? (256 - $failChance / $chanceMult) : 0;
			$event = ($d256 < $chance);
		}
		//check event type
		if($event)
		{
			//read monster data
			$sql = "SELECT * FROM location_monsters WHERE location_id=:id";
			$monsters = $this->_dbClient->selectAll($sql, array('id' => $this->location_id));
			//read special events
			$sql = "SELECT * FROM location_events WHERE location_id=:id";
			$events = $this->_dbClient->selectAll($sql, array('id' => $this->location_id));
			//find event data
			$chanceSum = 0;
			foreach($monsters as $row)
				$chanceSum += $row['chance'];
			foreach($events as $row)
				$chanceSum += $row['chance'];
			$d256 = mt_rand(0, 255);
			foreach($monsters as $row)
			{
				$chance = 256 * $row['chance'] / $chanceSum;
				if($d256 < $chance)
				{
					$eventParams = array('monsterId' => $row['monster_id']);
					break;
				}
				$d256 -= $chance;
			}
			foreach($events as $row)
			{
				$chance = 256 * $row['chance'] / $chanceSum;
				if($d256 < $chance)
				{
					$eventParams = array('eventId' => $row['event_id'], 'params' => $row['params']);
					break;
				}
				$d256 -= $chance;
			}
		}
		//store event
		if(!$eventParams)
			Daemon_MsgQueue::add('Brak zdarzeń w tej turze.');
		$this->_characterData->setLocationEvent($eventParams);
	}


	//counts characters present in a location
	public function getCharacterCount($limit)
	{
		$sql = "SELECT COUNT(1) FROM character_data WHERE location_id=:id LIMIT :limit";
		$params = array('id' => $this->location_id, 'limit' => $limit);
		return $this->_dbClient->selectValue($sql, $params);
	}


	//gets a list of characters present in a location
	public function getCharacters(Daemon_DbObject_CharacterData $char, $halfLimit = null)
	{
		if(empty($this->location_id))
			return array();
		$sql = "SELECT MAX(rollover_id) FROM rollovers";
		$rolloverId = $this->_dbClient->selectValue($sql);
		$charId = $this->_characterData->character_id;
		$cols = "c.character_id, c.name, cp.location_id, cp.level, cp.xp_used,
			c.clan_id, cp.faction_id, COALESCE(cp.rank_id, 0) AS rank_id, f.name AS faction_name";
		$tables = "character_data cp
			JOIN characters c USING(character_id)
			LEFT JOIN factions f USING(faction_id)";
		$params = array('id' => $this->location_id, 'charId' => $charId);
		if($halfLimit)
		{
			$params['xp'] = $char->xp_used;
			$params['halfLimit'] = $halfLimit;
			$sql = "(
				SELECT $cols FROM $tables
				WHERE cp.location_id=:id AND cp.character_id!=:charId AND cp.xp_used >= :xp
				ORDER BY xp_used ASC LIMIT $halfLimit
				) UNION (
				SELECT $cols FROM $tables
				WHERE cp.location_id=:id AND cp.character_id!=:charId AND cp.xp_used <= :xp
				ORDER BY xp_used DESC LIMIT $halfLimit
				) ORDER BY xp_used DESC, name ASC";
		}
		else
		{
			$sql = "SELECT $cols FROM $tables
				WHERE cp.location_id=:id AND cp.character_id!=:charId
				ORDER BY cp.xp_used DESC, c.name ASC";
		}
		$data = $this->_dbClient->selectAll($sql, $params);
		foreach($data as &$row)
		{
			$row['_canAttack'] = $char->canAttack($row, $rolloverId, false);
			$row['_sparring'] = ('normal' != $char->getCombatType($row, $this->type));
		}
		return $data;
	}


	//gets the name of a faction owning location/caern
	public function getFactionName()
	{
		if(empty($this->faction_id))
			return null;
		$sql = "SELECT name FROM factions WHERE faction_id=:id";
		return $this->_dbClient->selectValue($sql, array('id' => $this->faction_id));
	}


	//get a full list of maps
	public function getMaps()
	{
		$sql = "SELECT * FROM maps WHERE url IS NOT NULL AND url != '' ORDER BY sort, name";
		return $this->_dbClient->selectAll($sql, array());
	}


	//get a list of paths starting in current location
	public function getPaths()
	{
		if(empty($this->location_id))
			return array();
		$sql = "SELECT p.*, IF(p.name IS NULL OR p.name='', l.name, p.name) AS path_name,
				(p.cost_gold<=:gold AND p.cost_mana<=:mana) AS _enabled
			FROM location_paths p JOIN locations l ON p.destination_id=l.location_id
			WHERE p.location_id=:id ORDER BY l.name, l.location_id";
		$params = array('id' => $this->location_id,
			'gold' => $this->_characterData->gold_purse,
			'mana' => $this->_characterData->mana,
		);
		return $this->_dbClient->selectAll($sql, $params);
	}


	public function getPictureUrl()
	{
		if ($this->picture_url)
			return $this->picture_url;
		$sql = "SELECT picture_url FROM regions WHERE region_id=:id";
		return $this->_dbClient->selectValue($sql, array('id' => $this->region_id));
	}


	//get name of the location's region
	public function getRegionName()
	{
		if(empty($this->region_id))
			return null;
		$sql = "SELECT name FROM regions WHERE region_id=:id";
		return $this->_dbClient->selectValue($sql, array('id' => $this->region_id));
	}


	//get a list of available services
	public function getServices()
	{
		if(empty($this->location_id))
			return array();
		$sql = "SELECT s.*, (
				(s.faction_id IS NULL OR :factionId IS NULL OR s.faction_id = :factionId)
				AND (s.rank_id IS NULL OR s.rank_id <= :rankId)
			) AS _enabled
			FROM location_services l JOIN services s USING(service_id)
			WHERE l.location_id=:id ORDER BY s.name";
		$params = array('id' => $this->location_id,
			'factionId' => $this->_characterData->faction_id,
			'rankId' => $this->_characterData->rank_id,
		);
		return $this->_dbClient->selectAll($sql, $params);
	}


	//checks object data
	public function validate()
	{
		$this->chance1 = max(0, (int) $this->chance1);
		$this->chance2 = max(1, (int) $this->chance2);
		if($this->type != 'boss')
		{
			$this->boss_id = null;
			$this->boss_status = null;
			if ($this->type != 'caern')
				$this->faction_id = null;
		}
	}
}
