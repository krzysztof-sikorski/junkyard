<?php
//@author Krzysztof Sikorski
chdir(dirname(__FILE__));
$cfg = require_once './public/_init.php';
ini_set('expose_php', '0');
ini_set('default_mimetype', '');
ini_set('default_charset', '');
$dbClient = Daemon::createDbClient($cfg);
$dbCfg = new Daemon_DbConfig($dbClient);
$forum = new Daemon_Forum($dbClient);

//cleanup
$queries = array(
	"CREATE TEMPORARY TABLE junk(id INT NOT NULL PRIMARY KEY)",
	//delete inactive players
	"INSERT INTO junk(id) SELECT player_id FROM players WHERE last_login < (NOW() - INTERVAL 1 MONTH)",
	"DELETE FROM players WHERE player_id IN (SELECT id FROM junk)",
	"DELETE FROM user_agents WHERE player_id IN (SELECT id FROM junk)",
	"UPDATE characters SET player_id = NULL WHERE player_id IN (SELECT id FROM junk)",
	"TRUNCATE TABLE junk",
	//delete inactive characters
	"INSERT INTO junk(id) SELECT character_id FROM characters WHERE player_id IS NULL OR last_action < (NOW() - INTERVAL 1 MONTH)",
	"DELETE FROM characters WHERE character_id IN (SELECT id FROM junk)",
	"DELETE FROM character_data WHERE character_id IN (SELECT id FROM junk)",
	"DELETE FROM character_missions WHERE character_id IN (SELECT id FROM junk)",
	"DELETE FROM character_regions WHERE character_id IN (SELECT id FROM junk)",
	"DELETE FROM character_statistics WHERE character_id IN (SELECT id FROM junk)",
	"DELETE FROM character_titles WHERE character_id IN (SELECT id FROM junk)",
	"DELETE FROM combat_units WHERE combat_unit_id IN (SELECT CONCAT('character-', id) FROM junk)",
	"DELETE FROM inventory WHERE character_id IN (SELECT id FROM junk)",
	"UPDATE clans SET leader_id = NULL WHERE leader_id IN (SELECT id FROM junk)",
	"DELETE FROM clan_invitations WHERE character_id IN (SELECT id FROM junk)",
	"TRUNCATE TABLE junk",
	//dump abandoned clans & old invitations
	"DELETE FROM clans WHERE leader_id IS NULL",
	"DELETE FROM clan_invitations WHERE date_added < (NOW() - INTERVAL 1 WEEK)",
);
foreach($queries as $sql)
	$dbClient->query($sql, array());
unset($queries);

//check for endgame
if(!$dbCfg->rolloversEnabled)
	return;

//create rollover entry
$sql = "INSERT INTO rollovers(date_added) VALUES (now())";
$dbClient->query($sql);
$rolloverId = $dbClient->lastInsertId();

//give turns
$sql = "UPDATE character_data SET turns = LEAST(:limit, turns + :delta)";
$params = array('delta' => (int) $dbCfg->turnDelta, 'limit' => (int) $dbCfg->turnLimit);
$dbClient->query($sql, $params);

//run caern sieges
$sql = "SELECT l.location_id FROM locations l JOIN character_data cd USING(location_id)
	WHERE l.type='caern' AND cd.faction_id IS NOT NULL AND cd.faction_id != l.faction_id
	GROUP BY l.location_id";
$locations = $dbClient->selectColumn($sql);
foreach ((array) $locations as $siegeLocationId)
{
	$combat = new Daemon_CaernSiege();
	$combat->attachDbClient($dbClient);
	$combat->execute($siegeLocationId);
	$sql = "INSERT INTO battles(rollover_id, location_id, type, combat_log)
		VALUES (:rolloverId, :locationId, 'caern', :combatLog)";
	$params = array('rolloverId' => $rolloverId, 'locationId' => $siegeLocationId,
		'combatLog' => $combat->getCombatLog());
	$dbClient->query($sql, $params);
	$dbCfg->siegeLocationId = null;
	$siegeLocationId = null;
	unset($combat);
}

//update faction power
$decay = (float) $dbCfg->factionDecay;
$sql = "UPDATE factions f SET f.power = FLOOR(:decay * f.power) + COALESCE((
	SELECT SUM(l.faction_value) FROM locations l WHERE l.type='caern' AND l.faction_id=f.faction_id
), 0)";
$dbClient->query($sql, array('decay' => $decay));

//activate bosses
$sql = "SELECT MAX(level) As max_level, MAX(rank_id) AS max_rank FROM character_data";
$row = $dbClient->selectRow($sql, array());
$unlockBosses = ($row['max_level'] >= $dbCfg->bossUnlockLevel) && ($row['max_rank'] >= $dbCfg->bossUnlockRank);
if($unlockBosses)
{
	$dbClient->query("UPDATE locations SET boss_status='active' WHERE type='boss' AND boss_status != 'defeated'");
	if(!$dbCfg->endgame)
		$dbCfg->endgame = 1;
}

//run boss sieges
$sql = "SELECT location_id, name FROM locations WHERE type='boss' AND boss_status = 'active'";
$locations = $dbClient->selectAll($sql);
if($locations)
{
	$factionPowers = array();
	$sql = "SELECT faction_id, power FROM factions";
	foreach($dbClient->selectAll($sql) as $row)
		$factionPowers[$row['faction_id']] = $row['power'];
	foreach($locations as $row)
	{
		$combat = new Daemon_BossCombat();
		$combat->attachDbClient($dbClient);
		$combat->execute($row['location_id'], $factionPowers);
		$combatLog = $combat->getCombatLog();
		if($combatLog)
		{
			$sql = "INSERT INTO battles(rollover_id, location_id, type, combat_log)
				VALUES (:rolloverId, :locationId, 'boss', :combatLog)";
			$params = array('rolloverId' => $rolloverId, 'locationId' => $row['location_id'], 'combatLog' => $combatLog);
			$dbClient->query($sql, $params);
			$forum->addChat(null, 'public', "Siedziba bossa \"$row[name]\" zaatakowana!");
		}
	}
}


//check for ending
$factions = array();
$sql = "SELECT faction_id, name FROM factions";
foreach($dbClient->selectAll($sql) as $row)
	$factions[$row['faction_id']] = array('name' => $row['name'], 'active' => 0, 'defeated' => 0);
$sql = "SELECT faction_id, (boss_status!='defeated') AS active
	FROM locations WHERE type = 'boss' GROUP BY faction_id, active";
foreach($dbClient->selectAll($sql) as $row)
{
	if($row['active'])
		$factions[$row['faction_id']]['active']++;
	else
		$factions[$row['faction_id']]['defeated']++;
}
$active = array();
$defeated = array();
foreach($factions as $factionId => $row)
{
	if($row['active'] || !$row['defeated'])
		$active[$factionId] = $row['name'];
	else
		$defeated[$factionId] = $row['name'];
}
$endgame = (count($active) < 2);
if($endgame)
{
	//final messages
	$active = implode(', ', $active);
	switch($active)
	{
		case 'blue':
			$msg = "Rewolucja została stłumiona. Niech żyje Porządek!";
			break;
		case 'red':
			$msg = "Cesarz został obalony. Niech żyje Rewolucja!";
			break;
		default:
			$msg = "Wojna dobiegła końca, lecz brak w niej zwycięzców. Czas pokaże, kto zajmie miejsce dawnych potęg...";
	}
	$forum->addChat(null, 'public', $msg);
	//cleanup
	$dbCfg->globalMessage = $msg;
	$dbCfg->rolloversEnabled = 0;
	$dbCfg->turnDelta = 0;
	$dbCfg->defaultRespawn = '';
	$sql = "UPDATE character_data SET turns = 0, location_id = NULL";
	$dbClient->query($sql);
	$sql = "TRUNCATE TABLE character_regions";
	$dbClient->query($sql);
}

//update rollover data
$sql = "SELECT COUNT(1) FROM players";
$nPlayers = $dbClient->selectValue($sql);
$sql = "SELECT COUNT(1) FROM characters";
$nChars = $dbClient->selectValue($sql);
$sql = "SELECT COUNT(1) FROM clans";
$nClans = $dbClient->selectValue($sql);
$sql = "UPDATE rollovers SET players_total = :players, characters_total = :chars,
	clans_total = :clans WHERE rollover_id = :id";
$params = array('id' => $rolloverId, 'players' => $nPlayers, 'chars' => $nChars, 'clans' => $nClans);
$dbClient->query($sql, $params);
