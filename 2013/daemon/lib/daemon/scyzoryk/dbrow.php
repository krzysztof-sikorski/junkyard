<?php
//@author Krzysztof Sikorski
class Daemon_Scyzoryk_DbRow
{
	const TABLE_NAME = null;
	const INDEX_COL = null;
	const INDEX_COL2 = null;
	final public function __construct($params = null)
	{
		foreach((array) $params as $name => $value)
			if(property_exists($this, $name))
				$this->$name = Daemon::normalizeString($value, true);
		$this->validate();
	}
	public function validate() {}
}


class Daemon_Scyzoryk_DbRowFaction extends Daemon_Scyzoryk_DbRow
{
	const TABLE_NAME = 'factions';
	const INDEX_COL = 'faction_id';
	public $faction_id;
	public $name;
}


class Daemon_Scyzoryk_DbRowFactionRank extends Daemon_Scyzoryk_DbRow
{
	const TABLE_NAME = 'faction_ranks';
	const INDEX_COL = 'faction_id';
	const INDEX_COL2 = 'rank_id';
	public $faction_id;
	public $rank_id;
	public $min_points = 1;
	public $title_id = null;
}


class Daemon_Scyzoryk_DbRowLocationEvent extends Daemon_Scyzoryk_DbRow
{
	const TABLE_NAME = 'location_events';
	const INDEX_COL = 'location_id';
	const INDEX_COL2 = 'event_id';
	public $location_id;
	public $event_id;
	public $chance = 1;
	public $params = '';
}


class Daemon_Scyzoryk_DbRowLocationMonster extends Daemon_Scyzoryk_DbRow
{
	const TABLE_NAME = 'location_monsters';
	const INDEX_COL = 'location_id';
	const INDEX_COL2 = 'monster_id';
	public $location_id;
	public $monster_id;
	public $chance = 1;
}


class Daemon_Scyzoryk_DbRowLocationPath extends Daemon_Scyzoryk_DbRow
{
	const TABLE_NAME = 'location_paths';
	const INDEX_COL = 'location_id';
	const INDEX_COL2 = 'destination_id';
	public $location_id;
	public $destination_id;
	public $name = null;
	public $cost_gold = 0;
	public $cost_mana = 0;
}


class Daemon_Scyzoryk_DbRowLocationService extends Daemon_Scyzoryk_DbRow
{
	const TABLE_NAME = 'location_services';
	const INDEX_COL = 'location_id';
	const INDEX_COL2 = 'service_id';
	public $location_id;
	public $service_id;
}


class Daemon_Scyzoryk_DbRowMap extends Daemon_Scyzoryk_DbRow
{
	const TABLE_NAME = 'maps';
	const INDEX_COL = 'map_id';
	public $map_id;
	public $name;
	public $url = '';
}


class Daemon_Scyzoryk_DbRowMonsterDrop extends Daemon_Scyzoryk_DbRow
{
	const TABLE_NAME = 'monster_drops';
	const INDEX_COL = 'monster_id';
	const INDEX_COL2 = 'item_id';
	public $monster_id;
	public $item_id;
	public $chance = 1;
}


class Daemon_Scyzoryk_DbRowRegion extends Daemon_Scyzoryk_DbRow
{
	const TABLE_NAME = 'regions';
	const INDEX_COL = 'region_id';
	public $region_id;
	public $name;
	public $respawn_id = null;
	public $picture_url = null;
}


class Daemon_Scyzoryk_DbRowService extends Daemon_Scyzoryk_DbRow
{
	const TABLE_NAME = 'services';
	const INDEX_COL = 'service_id';
	public $service_id;
	public $name;
	public $type = 'npc';
	public $faction_id;
	public $rank_id;
	public $description = null;
}


class Daemon_Scyzoryk_DbRowServiceItem extends Daemon_Scyzoryk_DbRow
{
	const TABLE_NAME = 'service_items';
	const INDEX_COL = 'service_id';
	const INDEX_COL2 = 'item_id';
	public $service_id;
	public $item_id;
	public $type = 'normal';
	public $quantity = null;
}


class Daemon_Scyzoryk_DbRowTitle extends Daemon_Scyzoryk_DbRow
{
	const TABLE_NAME = 'titles';
	const INDEX_COL = 'title_id';
	public $title_id;
	public $name_f = '';
	public $name_m = '';
	public $name_n = '';
}
