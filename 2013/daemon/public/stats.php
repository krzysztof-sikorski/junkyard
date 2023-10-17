<?php
//@author Krzysztof Sikorski
$cfg = require_once'./_init.php';


class Daemon_Controller_Status extends Daemon_Controller
{
	protected $pageSubtitle = 'Status gry';
	protected $pageTemplatePath = 'stats.xml';


	protected function prepareView()
	{
		$vars = array('loginEnabled', 'registerEnabled', 'rolloversEnabled', 'turnDelta', 'turnLimit');
		foreach($vars as $name)
			$this->view->$name = $this->dbCfg->$name;
		$this->view->menu = $this->view->getStatisticsMenu('status');
		//factions
		$factions = array();
		$powers = array();
		$sql = "SELECT faction_id, name, power FROM factions ORDER BY name";
		foreach($this->dbClient->selectAll($sql) as $row)
		{
			$factions[$row['faction_id']] = array('name' => $row['name'], 'power' => $row['power'],
				'chars' => 0, 'caerns' => '', 'powerMult' => null);
			$powers[$row['faction_id']] = $row['power'];
		}
		foreach($factions as $factionId => &$row)
			$row['powerMult'] = sprintf('%.2f', 100 * Daemon_Math::factionPowerMult($factionId, $powers));
		unset($row, $powers);
		$sql = "SELECT faction_id, COUNT(character_id) AS n
			FROM character_data WHERE faction_id IS NOT NULL GROUP BY faction_id";
		foreach($this->dbClient->selectAll($sql) as $row)
		{
			if(isset($factions[$row['faction_id']]))
				$factions[$row['faction_id']]['chars'] = (int) $row['n'];
		}
		$sql = "SELECT faction_id, GROUP_CONCAT(name SEPARATOR ', ') AS names
			FROM locations WHERE faction_id IS NOT NULL AND type='caern' GROUP BY faction_id";
		foreach($this->dbClient->selectAll($sql) as $row)
		{
			if(isset($factions[$row['faction_id']]))
				$factions[$row['faction_id']]['caerns'] = $row['names'];
		}
		$this->view->factions = $factions;
		//caerns
		$sql = "SELECT l.name
			FROM locations l JOIN character_data cd USING(location_id)
			WHERE l.type='caern' AND cd.faction_id IS NOT NULL AND cd.faction_id != l.faction_id
			GROUP BY l.location_id ORDER BY l.name";
		$this->view->caernSieges = $this->dbClient->selectColumn($sql);
		//rollovers
		$rollovers = array();
		$sql = "SELECT rollover_id, players_total, characters_total, clans_total,
				date_format(date_added, '%Y-%m-%d %H:%i') AS date_added
			FROM rollovers ORDER BY rollover_id DESC LIMIT 10";
		foreach ($this->dbClient->selectAll($sql) as $row)
		{
			$row['_battles'] = array();
			$rollovers[$row['rollover_id']] = $row;
		}
		$sql = "SELECT b.battle_id, b.rollover_id, l.name
			FROM battles b LEFT JOIN locations l USING(location_id)
			WHERE b.rollover_id >= :id ORDER BY b.battle_id";
		$params = array('id' => min(array_keys($rollovers)));
		foreach ($this->dbClient->selectAll($sql, $params) as $row)
			$rollovers[$row['rollover_id']]['_battles'][$row['battle_id']] = $row['name'];
		$this->view->rollovers = $rollovers;
	}
}


$ctrl = new Daemon_Controller_Status($cfg);
$ctrl->execute();
