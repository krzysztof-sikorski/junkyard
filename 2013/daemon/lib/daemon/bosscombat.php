<?php
//@author Krzysztof Sikorski
class Daemon_BossCombat extends Daemon_CaernSiege
{
	public function execute($locationId, array $factionPowers = array())
	{
		//read location
		$location = $this->getLocation($locationId);
		$bossFactionId = $location['faction_id'];
		$powerMod = Daemon_Math::factionPowerMult($bossFactionId, $factionPowers);
		//prepare units
		$this->kickNeutrals($locationId);
		$units = $this->getCharacterUnits($locationId);
		//check for empty caern
		if(empty($units))
			return null;
		$attackers = false;
		foreach($units as $factionId => $faction)
		{
			if($factionId != $bossFactionId)
				$attackers = true;
		}
		if(!$attackers)
			return null;
		unset($attackers, $factionId, $faction);
		//prepare boss
		$boss = $this->getBossUnit($location['boss_id'], $bossFactionId, $powerMod);
		if(empty($boss))
			return null;
		$units[$bossFactionId][$boss->_id] = $boss;
		//add monster support
		$supportCount = 0;
		foreach($units as $factionId => $faction)
		{
			if($factionId != $bossFactionId)
				$supportCount += count($faction);
			else
				$supportCount -= count($faction);
		}
		if($supportCount > 0)
		{
			$support = $this->getLocationMonsters($locationId, $bossFactionId, $supportCount);
			foreach($support as $unit)
				$units[$bossFactionId][$unit->_id] = $unit;
		}
		unset($support, $supportCount);
		//execute combat
		$this->combatLog = $this->runCombat($units, $bossFactionId);
		//save characters
		$this->putCharacterUnits($units);
		//find winner
		$winnerId = $this->getWinnerFaction($units);
		//kick losers
		$this->kickEnemies($locationId, $winnerId);
		//update location, send message
		if($winnerId != $bossFactionId)
		{
			$sql = "UPDATE locations SET boss_status='defeated' WHERE location_id=:id";
			$params = array('id' => $locationId);
			$this->dbClient->query($sql, $params);
			$msg = "Boss $boss->name z lokacji $location[name] został pokonany!";
			$forum = new Daemon_Forum($this->dbClient);
			$forum->addChat(null, 'public', $msg);
		}
	}


	private function getBossUnit($monsterId, $factionId, $powerMod)
	{
		//read base stats
		$sql = "SELECT monster_id, name, combat_unit_id
			FROM monsters WHERE monster_id=:id";
		$row = $this->dbClient->selectRow($sql, array('id' => $monsterId));
		$unit = new Daemon_Combat_Unit();
		$unit->attachDbClient($this->dbClient);
		$unit->get(array('combat_unit_id' => $row['combat_unit_id']));
		$unit->name = $row['name'];
		$unit->faction_id = $factionId;
		$unit->_id = 'boss';
		//modify stats
		$modified = array(
			'str1', 'atk1', 'str2', 'atk2',
			'pdef', 'pres', 'mdef', 'mres',
			'speed', 'armor', 'regen', 'healthMax',
		);
		foreach($modified as $name)
			$unit->$name *= $powerMod;
		$unit->health = $unit->healthMax;
		return $unit;
	}


	private function getLocation($locationId)
	{
		$sql = "SELECT l.faction_id, l.boss_id, l.name, f.name AS faction_name
			FROM locations l JOIN factions f USING(faction_id)
			WHERE location_id = :id";
		return $this->dbClient->selectRow($sql, array('id' => $locationId));
	}
}
