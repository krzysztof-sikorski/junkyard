<?php
//@author Krzysztof Sikorski
class Daemon_Spell_Scout extends Daemon_SpellInterface
{
	public function execute($spellId, $cost)
	{
		if(!$this->updateCharacterMana($cost))
			return null;
		$location = $this->getLocationById($this->characterData->location_id);
		if(!$location->location_id)
		{
			Daemon_MsgQueue::add('Nie da się badać Otchłani!');
			return null;
		}
		$locations = array(0 => $location);
		$pathIds = $this->getPathsByLocationId($this->characterData->location_id);
		foreach($pathIds as $id)
			$locations[] = $this->getLocationById($id);
		ob_start();
		$this->view->locations = $locations;
		$this->view->display('spell/scout.xml');
		return ob_get_clean();
	}


	private function getLocationById($locationId)
	{
		$location = new Daemon_DbObject_Location;
		$location->attachDbClient($this->dbClient);
		$location->get(array('location_id' => $locationId));
		$location->_monsters = array();
		$location->_events = array();
		if($location->location_id)
		{
			$params = array('id' => $location->location_id);
			$sql = "SELECT m.name, m.level, lm.chance FROM location_monsters lm JOIN monsters m USING(monster_id)
				WHERE lm.location_id=:id ORDER BY m.name";
			foreach($this->dbClient->selectAll($sql, $params) as $row)
				$location->_monsters[] = sprintf('%s (poziom %d, częstość %d)', $row['name'], $row['level'], $row['chance']);
			$sql = "SELECT e.name, le.chance FROM location_events le JOIN events e USING(event_id)
				WHERE le.location_id=:id ORDER BY e.name";
			foreach($this->dbClient->selectAll($sql, $params) as $row)
				$location->_events[] = sprintf('%s (specjalne, częstość %d)', $row['name'], $row['chance']);
		}
		return $location;
	}


	private function getPathsByLocationId($locationId)
	{
		$sql = "SELECT destination_id FROM location_paths p
			JOIN locations l ON l.location_id = p.destination_id
			WHERE p.location_id=:id ORDER BY l.name, p.destination_id";
		return $this->dbClient->selectColumn($sql, array('id' => $locationId));
	}
}
