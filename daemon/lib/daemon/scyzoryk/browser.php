<?php
//@author Krzysztof Sikorski
class Daemon_Scyzoryk_Browser extends Daemon_Scyzoryk
{


	public function findRow($tableName, $indexCol, $id, $name)
	{
		$sql = "SELECT $indexCol AS id, name FROM $tableName
			WHERE $indexCol LIKE CONCAT('%', :id, '%') AND name LIKE CONCAT('%', :name, '%')";
		$params = array('id' => $id, 'name' => $name);
		return $this->dbClient->selectAll($sql, $params);
	}


	public function getCombatUnits(Daemon_Scyzoryk_Filter $filter = null)
	{
		$cond = array();
		$params = array();
		if($filter instanceof Daemon_Scyzoryk_Filter)
		{
			if($filter->id)
			{
				$cond[] = "combat_unit_id LIKE CONCAT('%', :id, '%')";
				$params['id'] = $filter->id;
			}
			if($filter->name)
			{
				$cond[] = "name LIKE CONCAT('%', :name, '%')";
				$params['name'] = $filter->name;
			}
		}
		$cond = $cond ? 'WHERE '.implode(' AND ', $cond) : null;
		$sql = "SELECT *, (combat_unit_id LIKE 'character-%') AS _character
			FROM combat_units $cond ORDER BY _character, combat_unit_id";
		return $this->dbClient->selectAll($sql, $params);
	}


	public function getFactionRanks($factionId)
	{
		$sql = "SELECT * FROM faction_ranks r LEFT JOIN titles USING(title_id) WHERE r.faction_id=:id ORDER BY rank_id";
		return $this->dbClient->selectAll($sql, array('id' => $factionId));
	}


	public function getFactions()
	{
		$sql = "SELECT * FROM factions ORDER BY faction_id";
		return $this->dbClient->selectAll($sql, array());
	}


	public function getItems(Daemon_Scyzoryk_Filter $filter)
	{
		$cond = array();
		$params = array();
		if($filter->id)
		{
			$cond[] = "item_id LIKE CONCAT('%', :id, '%')";
			$params['id'] = $filter->id;
		}
		if($filter->name)
		{
			$cond[] = "name LIKE CONCAT('%', :name, '%')";
			$params['name'] = $filter->name;
		}
		if($filter->type)
		{
			$cond[] = 'type = :type';
			$params['type'] = $filter->type;
		}
		$cond = $cond ? 'WHERE '.implode(' AND ', $cond) : null;
		$sql = "SELECT * FROM items $cond
			ORDER BY type, damage_type, suggested_value, value, item_id";
		return $this->dbClient->selectAll($sql, $params);
	}


	public function getItemTemplates()
	{
		$sql = "SELECT * FROM item_templates ORDER BY id";
		return $this->dbClient->selectAll($sql, array());
	}


	public function getLocationEvents($locationId)
	{
		$sql = "SELECT * FROM location_events
			WHERE location_id=:id ORDER BY event_id";
		return $this->dbClient->selectAll($sql, array('id' => $locationId));
	}


	public function getLocationMonsters($locationId)
	{
		$sql = "SELECT l.*, m.name AS monster_name
			FROM location_monsters l
			LEFT JOIN monsters m USING(monster_id)
			WHERE l.location_id=:id ORDER BY l.monster_id";
		return $this->dbClient->selectAll($sql, array('id' => $locationId));
	}


	public function getLocationPaths($locationId)
	{
		$sql = "SELECT p.*, loc.name AS destination_name
			FROM location_paths p
			LEFT JOIN locations loc ON p.destination_id = loc.location_id
			WHERE p.location_id=:id ORDER BY p.destination_id";
		return $this->dbClient->selectAll($sql, array('id' => $locationId));
	}


	public function getLocationServices($locationId)
	{
		$sql = "SELECT l.*, s.name AS service_name, s.type AS service_type
			FROM location_services l
			LEFT JOIN services s USING(service_id)
			WHERE l.location_id=:id ORDER BY s.service_id";
		return $this->dbClient->selectAll($sql, array('id' => $locationId));
	}


	public function getLocations(Daemon_Scyzoryk_Filter $filter)
	{
		$cond = array();
		$params = array();
		if($filter->id)
		{
			$cond[] = "l.location_id LIKE CONCAT('%', :id, '%')";
			$params['id'] = $filter->id;
		}
		if($filter->name)
		{
			$cond[] = "l.name LIKE CONCAT('%', :name, '%')";
			$params['name'] = $filter->name;
		}
		if($filter->region_id)
		{
			$cond[] = 'l.region_id = :region_id';
			$params['region_id'] = $filter->region_id;
		}
		$cond = $cond ? 'WHERE '.implode(' AND ', $cond) : null;
		$sql = "SELECT l.*, r.name AS region_name,
			( SELECT GROUP_CONCAT(lp.destination_id SEPARATOR ',')
				FROM location_paths lp WHERE lp.location_id=l.location_id ) AS paths,
			( SELECT GROUP_CONCAT(lm.monster_id SEPARATOR ',')
				FROM location_monsters lm WHERE lm.location_id=l.location_id ) AS monsters
			FROM locations l LEFT JOIN regions r USING(region_id) $cond ORDER BY l.region_id, l.location_id";
		$data = $this->dbClient->selectAll($sql, $params);
		foreach ($data as &$row)
		{
			$row['paths'] = explode(',', $row['paths']);
			$row['monsters'] = explode(',', $row['monsters']);
		}
		return $data;
	}


	public function getMaps()
	{
		$sql = "SELECT * FROM maps ORDER BY sort, map_id";
		return $this->dbClient->selectAll($sql, array());
	}


	public function getMonsterDrops($monsterId)
	{
		$sql = "SELECT m.*, i.name FROM monster_drops m LEFT JOIN items i USING(item_id) WHERE m.monster_id=:id";
		return $this->dbClient->selectAll($sql, array('id' => $monsterId));
	}


	public function getMonsters(Daemon_Scyzoryk_Filter $filter)
	{
		$cond = array();
		$params = array();
		if($filter->id)
		{
			$cond[] = "m.monster_id LIKE CONCAT('%', :id, '%')";
			$params['id'] = $filter->id;
		}
		if($filter->name)
		{
			$cond[] = "m.name LIKE CONCAT('%', :name, '%')";
			$params['name'] = $filter->name;
		}
		if($filter->class)
		{
			$cond[] = "m.class = :class";
			$params['class'] = $filter->class;
		}
		$cond = $cond ? 'WHERE '.implode(' AND ', $cond) : null;
		$sql = "SELECT m.*, ( SELECT GROUP_CONCAT(md.item_id SEPARATOR ', ')
				FROM monster_drops md WHERE md.monster_id=m.monster_id ) AS drops
			FROM monsters m $cond ORDER BY m.level, m.monster_id";
		$data = $this->dbClient->selectAll($sql, $params);
		foreach ($data as &$row)
			$row['drops'] = explode(',', $row['drops']);
		return $data;
	}


	public function getRegions()
	{
		$sql = "SELECT r.*, l.name AS respawn_name
			FROM regions r LEFT JOIN locations l ON l.location_id = r.respawn_id
			ORDER BY r.region_id";
		return $this->dbClient->selectAll($sql, array());
	}


	public function getServiceItems($serviceId)
	{
		$sql = "SELECT s.*, i.name, s.type='drop' AS _drop
			FROM service_items s LEFT JOIN items i USING(item_id)
			WHERE s.service_id=:id ORDER BY type, item_id";
		return $this->dbClient->selectAll($sql, array('id' => $serviceId));
	}


	public function getServices()
	{
		$sql = "SELECT * FROM services ORDER BY service_id";
		return $this->dbClient->selectAll($sql, array());
	}


	public function getTitles()
	{
		$sql = "SELECT * FROM titles ORDER BY title_id";
		return $this->dbClient->selectAll($sql, array());
	}
}
