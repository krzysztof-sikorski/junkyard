<?php
//@author Krzysztof Sikorski
class Daemon_CaernSiege
{
	protected $dbClient = null;
	protected $combatLog;


	protected function addCharacters(Daemon_Combat $combat, array $units, $locationFactionId)
	{
		foreach($units as $factionId => $faction)
		{
			foreach($faction as $unit)
			{
				$attacker = ($factionId != $locationFactionId);
				$combat->addUnit($unit->_id, $unit, $attacker);
			}
		}
	}


	public function attachDbClient(Daemon_DbClient $dbClient)
	{
		$this->dbClient = $dbClient;
	}


	//executes the siege, generates the report
	public function execute($locationId)
	{
		//prepare units
		$this->kickNeutrals($locationId);
		$faction = $this->getLocationFaction($locationId);
		$caernFactionId = $faction['id'];
		$units = $this->getCharacterUnits($locationId);
		//check for empty caern
		if(empty($units))
		{
			$this->combatLog = '<p>Caern utracony z braku obrońców.</p>';
			$winnerId = null;
		}
		else
		{
			//add monster support
			$supportCount = 0;
			foreach($units as $factionId => $faction)
			{
				if($factionId != $caernFactionId)
					$supportCount += count($faction);
				else
					$supportCount -= count($faction);
			}
			if($supportCount > 0)
			{
				$support = $this->getLocationMonsters($locationId, $caernFactionId, $supportCount);
				foreach($support as $unit)
					$units[$caernFactionId][$unit->_id] = $unit;
			}
			unset($support, $supportCount);
			//execute combat
			$this->combatLog = $this->runCombat($units, $caernFactionId);
			//save characters
			$this->putCharacterUnits($units);
			//find winner (units modified by combat)
			$winnerId = $this->getWinnerFaction($units);
			if(!$winnerId)
				$msg = 'utracony';
			elseif($winnerId != $caernFactionId)
				$msg = 'przejęty';
			else
				$msg = 'utrzymany';
			$this->combatLog .= "<p><b>Caern $msg!</b></p>";
		}
		//kick losers
		$this->kickEnemies($locationId, $winnerId);
		//update location
		$sql = "UPDATE locations SET faction_id=:fid WHERE location_id=:id";
		$params = array('fid' => $winnerId, 'id' => $locationId);
		$this->dbClient->query($sql, $params);
	}


	protected function getCharacterUnits($locationId)
	{
		$units = array();
		$sql = "SELECT cd.character_id, c.name, cd.faction_id, cd.health, cd.health_max, cd.combat_unit_id
			FROM character_data cd JOIN characters c USING(character_id)
			WHERE location_id=:id AND cd.faction_id IS NOT NULL ORDER BY xp_used DESC";
		$data = $this->dbClient->selectAll($sql, array('id' => $locationId));
		foreach($data as $row)
		{
			$unit = new Daemon_Combat_Unit();
			$unit->attachDbClient($this->dbClient);
			$unit->get(array('combat_unit_id' => $row['combat_unit_id']));
			$unit->name = $row['name'];
			$unit->faction_id = $row['faction_id'];
			$unit->health = $row['health_max'];
			$unit->health_max = $row['health_max'];
			$unit->_id = $row['character_id'];
			$units[$row['faction_id']][$unit->_id] = $unit;
		}
		return $units;
	}


	public function getCombatLog()
	{
		return $this->combatLog;
	}


	protected function getLocationFaction($locationId)
	{
		$sql = "SELECT faction_id AS id, f.name
			FROM locations l JOIN factions f USING(faction_id)
			WHERE location_id=:id";
		return $this->dbClient->selectRow($sql, array('id' => $locationId));
	}


	protected function getLocationMonsters($locationId, $factionId, $desiredCount)
	{
		$result = array();
		$sql = "SELECT m.monster_id, m.name, m.combat_unit_id
			FROM location_monsters lm JOIN monsters m USING(monster_id)
			WHERE location_id=:id";
		$mobs = $this->dbClient->selectAll($sql, array('id' => $locationId));
		if(empty($mobs))
			return array();
		for($i = 0; $i < $desiredCount; ++$i)
		{
			$row = $mobs[array_rand($mobs)];
			$unit = new Daemon_Combat_Unit();
			$unit->attachDbClient($this->dbClient);
			$unit->get(array('combat_unit_id' => $row['combat_unit_id']));
			$unit->name = $row['name'];
			$unit->faction_id = $factionId;
			$unit->_id = "mob_$i";
			$result[$unit->_id] = $unit;
		}
		return $result;
	}


	protected function getWinnerFaction(array $units)
	{
		$survivors = array();
		foreach($units as $factionId => $faction)
		{
			$survivors[$factionId] = 0;
			foreach($faction as $unit)
			{
				if($unit->health > 0)
					$survivors[$unit->faction_id] += 1;
			}
		}
		$winnerId = null;
		$winnerCount = 0;
		foreach($survivors as $factionId => $count)
		{
			if($winnerCount < $count)
			{
				$winnerId = $factionId;
				$winnerCount = $count;
			}
		}
		return $winnerId;
	}


	//kicks enemies out of the caern
	public function kickEnemies($locationId, $factionId)
	{
		$sql = "UPDATE character_data SET location_id=NULL WHERE location_id=:locationId";
		$params = array('locationId' => $locationId);
		if($factionId)
		{
			$sql .= " AND faction_id!=:factionId";
			$params['factionId'] = $factionId;
		}
		$this->dbClient->query($sql, $params);
	}


	//kicks neutral characters out of the caern
	protected function kickNeutrals($locationId)
	{
		$sql = "UPDATE character_data SET location_id=NULL WHERE location_id=:id AND faction_id IS NULL";
		$this->dbClient->query($sql, array('id' => $locationId));
	}


	protected function putCharacterUnits(array $units)
	{
		$sqlLive = "UPDATE character_data SET health=:hp WHERE character_id=:id";
		$sqlDie = "UPDATE character_data SET health=0, location_id=null WHERE character_id=:id";
		foreach($units as $faction)
		{
			foreach($faction as $unit)
			{
				if(!is_numeric($unit->_id))
					continue;//monsters have string IDs
				if($unit->health > 0)
				{
					$params = array('id' => $unit->_id, 'hp' => $unit->health);
					$this->dbClient->query($sqlLive, $params);
				}
				else
				{
					$params = array('id' => $unit->_id);
					$this->dbClient->query($sqlDie, $params);
				}
			}
		}
	}


	public function runCombat(array $units, $locationFactionId)
	{
		$combat = new Daemon_Combat();
		$logger = new Daemon_Combat_Log();
		$combat->attachLogger($logger);
		foreach($units as $factionId => $faction)
		{
			foreach($faction as $unit)
			{
				$attacker = ($factionId != $locationFactionId);
				$combat->addUnit($unit->_id, $unit, $attacker);
			}
		}
		$combat->execute();
		foreach($combat->units as &$unit)
			$unit->health = max(0, ceil($unit->health));
		//prepare summary
		$summary = array();
		$summary[] = '<table class="border">';
		$summary[] = '<caption>Podsumowanie bitwy</caption>';
		$summary[] = '<tr>';
		$summary[] = '<th rowspan="2">Strona</th><th colspan="3">Postać</th>';
		$summary[] = '<th colspan="3">Wykonane ataki</th><th colspan="3">Otrzymane ataki</th>';
		$summary[] = '</tr>';
		$summary[] = '<tr>';
		$summary[] = '<th>Imię</th><th>Frakcja</th><th>Zdrowie</th>';
		$summary[] = '<th>Ataki</th><th>Obrażenia</th><th>Średnia</th>';
		$summary[] = '<th>Ataki</th><th>Obrażenia</th><th>Średnia</th>';
		$summary[] = '</tr>';
		foreach($units as $factionId => $faction)
		{
			foreach($faction as $unit)
			{
				$attackerTxt = $unit->_attacker ? 'atak' : 'obrona';
				$dmgTotal = $unit->_dmgDealt + $unit->_dmgTaken;
				$health = max(0, ceil($unit->health_max - $unit->_dmgTaken));
				$healthPercent = $unit->health_max ? round(100 * $health / $unit->health_max, 2) : 0;
				$avgDealt = $unit->_cntDealt ? $unit->_dmgDealt / $unit->_cntDealt : null;
				$avgTaken = $unit->_cntTaken ? $unit->_dmgTaken / $unit->_cntTaken : null;
				$summary[] = '<tr>';
				$summary[] = sprintf('<td>%s</td><td>%s</td><td>%s</td><td>%.2f%%</td>',
					$attackerTxt, $unit->name, $unit->faction_id, $healthPercent);
				$summary[] = sprintf('<td>%d</td><td>%.3f</td><td>%.3f</td>',
					$unit->_cntDealt, $unit->_dmgDealt, $avgDealt);
				$summary[] = sprintf('<td>%d</td><td>%.3f</td><td>%.3f</td>',
					$unit->_cntTaken, $unit->_dmgTaken, $avgTaken);
				$summary[] = '</tr>';
			}
		}
		$summary[] = '</table>';
		return implode('', $summary) . (string) $logger;
	}
}
