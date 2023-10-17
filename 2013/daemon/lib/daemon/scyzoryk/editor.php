<?php
//@author Krzysztof Sikorski
class Daemon_Scyzoryk_Editor extends Daemon_Scyzoryk
{


	public function deleteCombatUnits(array $ids)
	{
		$this->deleteRows('combat_units', 'combat_unit_id', $ids);
	}


	public function deleteFactionRanks($factionId, array $ids)
	{
		$sql = "DELETE FROM faction_ranks WHERE faction_id=:factionId AND rank_id=:id";
		foreach($ids as $id)
			$this->dbClient->query($sql, array('factionId' => $factionId, 'id' => $id));
	}


	public function deleteFactions(array $ids)
	{
		$this->deleteRows('factions', 'faction_id', $ids);
		$this->deleteRows('faction_ranks', 'faction_id', $ids);
	}


	public function deleteItems(array $ids)
	{
		$this->deleteRows('items', 'item_id', $ids);
		$this->deleteRows('service_items', 'item_id', $ids);
		$this->deleteRows('monster_drops', 'item_id', $ids);
	}


	public function deleteItemTemplates(array $ids)
	{
		$this->deleteRows('item_templates', 'id', $ids);
	}


	public function deleteLocationEvents($locationId, array $ids)
	{
		$sql = "DELETE FROM location_events WHERE location_id=:locationId AND event_id=:id";
		foreach($ids as $id)
			$this->dbClient->query($sql, array('locationId' => $locationId, 'id' => $id));
	}


	public function deleteLocationMonsters($locationId, array $ids)
	{
		$sql = "DELETE FROM location_monsters WHERE location_id=:locationId AND monster_id=:id";
		foreach($ids as $id)
			$this->dbClient->query($sql, array('locationId' => $locationId, 'id' => $id));
	}


	public function deleteLocationPaths($srcId, array $dst, array $rev = array())
	{
		$sql = "DELETE FROM location_paths WHERE location_id=:src AND destination_id=:dst";
		foreach($dst as $key => $id)
		{
			$this->dbClient->query($sql, array('src' => $srcId, 'dst' => $id));
			if(!empty($rev[$key]))
				$this->dbClient->query($sql, array('src' => $id, 'dst' => $srcId));
		}
	}


	public function deleteLocationServices($locationId, array $ids)
	{
		$sql = "DELETE FROM location_services WHERE location_id=:locationId AND service_id=:id";
		foreach($ids as $id)
			$this->dbClient->query($sql, array('locationId' => $locationId, 'id' => $id));
	}


	public function deleteLocations(array $ids)
	{
		$this->deleteRows('locations', 'location_id', $ids);
		$this->deleteRows('location_events', 'location_id', $ids);
		$this->deleteRows('location_monsters', 'location_id', $ids);
		$this->deleteRows('location_paths', 'location_id', $ids);
		$this->deleteRows('location_paths', 'destination_id', $ids);
		$this->deleteRows('location_services', 'location_id', $ids);
	}


	public function deleteMaps(array $ids)
	{
		$this->deleteRows('maps', 'map_id', $ids);
	}


	public function deleteMonsterDrops($monsterId, array $ids)
	{
		$sql = "DELETE FROM monster_drops WHERE monster_id=:monsterId AND item_id=:id";
		foreach($ids as $id)
			$this->dbClient->query($sql, array('monsterId' => $monsterId, 'id' => $id));
	}


	public function deleteMonsters(array $ids)
	{
		$this->deleteRows('monsters', 'monster_id', $ids);
		$this->deleteRows('monster_drops', 'monster_id', $ids);
		$this->deleteRows('location_monsters', 'monster_id', $ids);
	}


	public function deleteRegions(array $ids)
	{
		$this->deleteRows('regions', 'region_id', $ids);
		$sql = "UPDATE locations SET region_id = null WHERE region_id=:id";
		foreach($ids as $id)
			$this->dbClient->query($sql, array('id' => $id));
	}


	public function deleteServiceItems($serviceId, array $ids)
	{
		$sql = "DELETE FROM service_items WHERE service_id=:serviceId AND item_id=:id";
		foreach($ids as $id)
			$this->dbClient->query($sql, array('serviceId' => $serviceId, 'id' => $id));
	}


	public function deleteServices(array $ids)
	{
		$this->deleteRows('services', 'service_id', $ids);
		$this->deleteRows('service_items', 'service_id', $ids);
	}


	public function deleteTitles(array $ids)
	{
		$this->deleteRows('titles', 'title_id', $ids);
	}
}
