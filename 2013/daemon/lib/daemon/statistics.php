<?php
//@author Krzysztof Sikorski
//viewer for world statistics (characters, clans etc)
class Daemon_Statistics
{
	private $dbClient;
	private $charactersOrderTypes = array(
		'name' => array('key' => 'name', 'sort' => 'name ASC', 'from' => 'name >= :from'),
		'lvl' => array('key' => 'level', 'sort' => 'level DESC', 'from' => 'level <= :from'),
		'xp' => array('key' => 'xp_used', 'sort' => 'xp_used DESC', 'from' => 'xp_used <= :from'),
		'fac' => array('key' => 'faction_id', 'sort' => 'faction_id ASC', 'from' => 'faction_id >= :from'),
		'clan' => array('key' => 'clan_id', 'sort' => 'clan_id ASC', 'from' => 'clan_id >= :from'),
		'date' => array('key' => 'date_created', 'sort' => 'date_created ASC', 'from' => 'date_created >= :from'),
		'last' => array('key' => 'last_action', 'sort' => 'last_action DESC', 'from' => 'last_action >= :from'),
		'win' => array('key' => 'duel_wins', 'sort' => 'duel_wins DESC', 'from' => 'duel_wins <= :from'),
		'los' => array('key' => 'duel_losses', 'sort' => 'duel_losses DESC', 'from' => 'duel_losses <= :from'),
	);


	public function __construct(Daemon_DbClient $dbClient)
	{
		$this->dbClient = $dbClient;
	}


	public function getBattleById($battleId)
	{
		$sql = "SELECT b.combat_log, b.type, l.name AS location_name
			FROM battles b
			LEFT JOIN locations l USING(location_id)
			WHERE b.battle_id = :battleId";
		$params = array('battleId' => $battleId);
		return $this->dbClient->selectRow($sql, $params);
	}


	public function getBattles($limit, $from)
	{
		$params = array('limit' => $limit + 1);
		$cond = '';
		if($from)
		{
			$cond = 'WHERE battle_id <= :from';
			$params['from'] = (int) $from;
		}
		$sql = "SELECT b.battle_id, b.rollover_id, b.location_id, b.type, l.name AS location_name,
				IF(b.combat_log IS NULL OR b.combat_log = '', 0, 1) AS log_exists
			FROM battles b
			LEFT JOIN locations l USING(location_id)
			$cond ORDER BY battle_id DESC LIMIT :limit";
		return $this->getItemList($sql, $params, $limit, 'battle_id');
	}


	//fetches character data by ID
	public function getCharacterById($characterId)
	{
		$params = array('characterId' => $characterId);
		$sql = "SELECT c.player_id, p.name AS player_name, c.show_player,
				c.name, c.gender, c.clan_id, cp.level, cp.xp_used,
				c.avatar_url, c.quote, c.description, date_format(c.date_created, '%Y-%m-%d') AS date_created,
				cl.name AS clan_name, f.name AS faction_name, COALESCE(cp.rank_id, 0) AS rank_id,
				CASE c.gender WHEN 'f' THEN frt.name_f WHEN 'm' THEN frt.name_m ELSE frt.name_n END AS rank_name
			FROM characters c
			LEFT JOIN players p USING(player_id)
			LEFT JOIN character_data cp USING(character_id)
			LEFT JOIN factions f USING(faction_id)
			LEFT JOIN faction_ranks fr USING(faction_id, rank_id)
			LEFT JOIN titles frt USING(title_id)
			LEFT JOIN clans cl USING(clan_id)
			WHERE character_id = :characterId";
		if($character = $this->dbClient->selectRow($sql, $params))
		{
			$sql = "SELECT CASE c.gender WHEN 'f' THEN t.name_f WHEN 'm' THEN t.name_m WHEN 'n' THEN t.name_n END AS title
				FROM character_titles ct
				JOIN characters c ON c.character_id=ct.character_id
				JOIN titles t ON ct.title_id=t.title_id
				WHERE ct.character_id = :characterId ORDER BY title ASC";
			if($titles = $this->dbClient->selectColumn($sql, $params))
				$character['titles'] = implode(', ', $titles);
			else $character['titles'] = null;
			$sql = "SELECT * FROM character_statistics WHERE character_id = :characterId";
			$character['statistics'] = $this->dbClient->selectRow($sql, $params);
		}
		return $character;
	}


	//fetches a list of characters
	public function getCharacters($limit, $from, $order, $clanId)
	{
		if(!isset($this->charactersOrderTypes[$order]))
			$order = 'xp';
		$orderParams = $this->charactersOrderTypes[$order];

		$from = explode(',', $from, 2);
		if(count($from)<2)
			$from = array();

		$params = array('limit' => $limit + 1);
		$where = array("$orderParams[key] IS NOT NULL", "c.character_id!=392");
		if($clanId)
		{
			$params['clanId'] = $clanId;
			$where[] = 'clan_id=:clanId';
		}
		if($from)
		{
			$params['from'] = $from[1];
			$params['characterId'] = $from[0];
			$where[] = "($orderParams[from] AND character_id>= :characterId)";
		}
		if($where)
			$where = 'WHERE ' . implode(' AND ', $where);
		else $where = '';
		$orderClause = $orderParams['sort'];
		$sql = "SELECT c.character_id, c.name, c.clan_id, cp.level, cp.xp_used,
				cp.faction_id, COALESCE(cp.rank_id, 0) AS rank_id,
				date_format(c.date_created, '%Y-%m-%d') AS date_created,
				date_format(c.last_action, '%Y-%m-%d') AS last_action,
				cs.duel_wins, cs.duel_losses, cs.kills_mob1, cs.kills_mob2, cs.kills_mob3, cs.kills_mob4
			FROM characters c
			LEFT JOIN character_data cp USING(character_id)
			LEFT JOIN character_statistics cs USING(character_id)
			$where ORDER BY $orderClause, character_id ASC LIMIT :limit";
		$list =  $this->dbClient->selectAll($sql, $params);
		if(count($list) > $limit)
		{
			$next = array_pop($list);
			$next = sprintf('%s,%s', $next['character_id'], $next[$orderParams['key']]);
		}
		else $next = null;
		return array('list' => $list, 'next' => $next);
	}


	//fetches clan data by ID
	public function getClanById($clanId)
	{
		$params = array('clanId' => $clanId);
		$sql = "SELECT cl.*, c.name AS leader_name,
				date_format(cl.date_created, '%Y-%m-%d') AS date_created
			FROM clans cl LEFT JOIN characters c ON c.character_id=leader_id
			WHERE cl.clan_id = :clanId";
		if($clan = $this->dbClient->selectRow($sql, $params))
		{
			$sql = "SELECT COUNT(1) FROM characters WHERE clan_id = :clanId";
			$clan['members'] = $this->dbClient->selectColumn($sql, $params);
		}
		return $clan;
	}


	//fetches a list of clans
	public function getClans($limit, $from)
	{
		$params = array('limit' => $limit + 1);
		$cond = '';
		if($from)
		{
			$cond = 'WHERE cl.clan_id >= :from';
			$params['from'] = $from;
		}
		$sql = "SELECT cl.*, c.name AS leader_name, n.members,
				date_format(cl.date_created, '%Y-%m-%d') AS date_created
			FROM clans cl LEFT JOIN characters c ON c.character_id=leader_id
			LEFT JOIN (
				SELECT clan_id, COUNT(1) AS members FROM characters WHERE clan_id IS NOT NULL GROUP BY clan_id
			) AS n ON n.clan_id=cl.clan_id
			$cond ORDER BY cl.clan_id ASC LIMIT :limit";
		return $this->getItemList($sql, $params, $limit, 'clan_id');
	}


	//fetches combat log by duelId
	public function getDuelById($characterId, $duelId)
	{
		$sql = "SELECT d.combat_log, ca.name AS attacker_name, cb.name AS defender_name
			FROM duels d
			LEFT JOIN characters ca ON ca.character_id=d.attacker_id
			LEFT JOIN characters cb ON cb.character_id=d.defender_id
			WHERE d.duel_id = :duelId AND (d.attacker_id=:characterId OR d.defender_id=:characterId)";
		$params = array('characterId' => $characterId, 'duelId' => $duelId);
		return $this->dbClient->selectRow($sql, $params);
	}


	//fetches a list of duels, optionally filtered by character
	public function getDuels($limit, $from, $characterId, $viewerId)
	{
		$params = array('limit' => $limit + 1, 'vid1' => $viewerId, 'vid2' => $viewerId);
		$where = array();
		if($characterId)
		{
			$params['characterId'] = $characterId;
			$where[] = '(attacker_id=:characterId OR defender_id=:characterId)';
		}
		if($from)
		{
			$params['from'] = (int) $from;
			$where[] = 'duel_id <= :from';
		}
		if($where)
			$where = 'WHERE ' . implode(' AND ', $where);
		else $where = '';
		$sql = "SELECT d.duel_id,
				date_format(d.date_added, '%Y-%m-%d %H:%i:%s') AS date_added,
				d.rollover_id, d.attacker_id, d.defender_id, d.type, d.winner,
				(
					d.combat_log IS NOT NULL AND d.combat_log != ''
					AND (d.attacker_id = :vid1 OR d.defender_id = :vid2)
				) AS log_exists,
				ca.name AS attacker_name, cb.name AS defender_name
			FROM duels d
			LEFT JOIN characters ca ON ca.character_id=d.attacker_id
			LEFT JOIN characters cb ON cb.character_id=d.defender_id
			$where ORDER BY d.duel_id DESC LIMIT :limit";
		return $this->getItemList($sql, $params, $limit, 'duel_id');
	}


	private function getItemList($sql, array $params, $limit, $indexCol)
	{
		$list =  $this->dbClient->selectAll($sql, $params);
		if(count($list) > $limit)
		{
			$next = array_pop($list);
			$next = $next[$indexCol];
		}
		else $next = null;
		return array('list' => $list, 'next' => $next);
	}
}
