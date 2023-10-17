<?php
//@author Krzysztof Sikorski
class Daemon_DbObject_CharacterData extends Daemon_DbObject
{
	protected $_tableName = 'character_data';
	protected $_index = array('character_id');
	public $_characterName = null;
	public $_gender = null;
	public $character_id;
	public $location_id;
	public $faction_id, $faction_points, $rank_id;
	public $turns;
	public $gold_purse, $gold_bank;
	public $level, $xp_free, $xp_used;
	public $deaths, $health, $health_max;
	public $mana, $mana_max, $mana_regen;
	public $a_str, $a_dex, $a_vit, $a_pwr, $a_wil;
	public $s_pstr, $s_patk, $s_pdef, $s_pres, $s_preg;
	public $s_mstr, $s_matk, $s_mdef, $s_mres, $s_mreg;
	public $sp_scout, $sp_identify, $sp_vchar, $sp_vmonster, $sp_vitem;
	protected $location_event; //json packed
	public $combat_unit_id;
	private $_combatUnit;


	//executes selected event, returns event log
	public function attack(Daemon_View $view, $targetId, $locationType)
	{
		if(!$this->checkTurnCosts())
			return null;
		$combat = new Daemon_Duel();
		$combat->attachCharacterData($this);
		$combat->attachDbClient($this->_dbClient);
		$combat->execute($view, $locationType, $targetId);
		return $combat->getCombatLog();
	}


	//checks of character can attack selected target
	public function canAttack(array $target, $rolloverId, $withMessages)
	{
		//check Id
		if($this->character_id == $target['character_id'])
		{
			if($withMessages)
				Daemon_MsgQueue::add('Samobójstwo?');
			return false;
		}
		//check location
		if($this->location_id != $target['location_id'])
		{
			if($withMessages)
				Daemon_MsgQueue::add('Cel opuścił już tą lokację.');
			return false;
		}
		//check levels
		if(4 * $this->xp_used > 5 * $target['xp_used'])
		{
			if($withMessages)
				Daemon_MsgQueue::add('Cel jest za słaby.');
			return false;
		}
		if(5 * $this->xp_used < 4 * $target['xp_used'])
		{
			if($withMessages)
				Daemon_MsgQueue::add('Cel jest za silny.');
			return false;
		}
		//check previous attacks
		$cond = "attacker_id=:attackerId AND defender_id=:defenderId";
		$params = array('attackerId' => $this->character_id, 'defenderId' => $target['character_id']);
		if($rolloverId)
		{
			$cond .= " AND rollover_id=:rolloverId";
			$params['rolloverId'] = $rolloverId;
		}
		$sql = "SELECT COUNT(duel_id) FROM duels WHERE $cond";
		if($this->_dbClient->selectValue($sql, $params))
		{
			if($withMessages)
				Daemon_MsgQueue::add('Cel już był atakowany przez ciebie w tym przeliczeniu.');
			return false;
		}
		return true;
	}


	//updates turns, gold & mana; returns check result
	public function checkTurnCosts($costGold = 0, $costMana = 0)
	{
		if($this->turns < 1)
		{
			Daemon_MsgQueue::add('Nie masz dostępnych tur.');
			return false;
		}
		if($this->gold_purse < $costGold)
		{
			Daemon_MsgQueue::add('Masz za mało zlota.');
			return false;
		}
		if($this->mana < $costMana)
		{
			Daemon_MsgQueue::add('Masz za mało zlota.');
			return false;
		}
		$this->turns -= 1;
		$this->gold_purse -= $costGold;
		$this->mana -= $costMana;
		return true;
	}


	//checks of target can be attacked, returns combat type (null for error)
	public function getCombatType(array $target, $locationType)
	{
		//check location type
		if('normal' != $locationType)
			return 'arena';
		//check factions
		if($this->faction_id && ($this->faction_id == $target['faction_id']))
			return 'arena';
		//no restrictions, normal fight
		return 'normal';
	}


	//returns Daemon_DbObject_CombatUnit instance
	public function getCombatUnit($full = false)
	{
		if(!$this->_combatUnit)
		{
			$this->_combatUnit = $full ? new Daemon_Combat_Unit() : new Daemon_DbObject_CombatUnit();
			$this->_combatUnit->attachDbClient($this->_dbClient);
			if($this->character_id)
			{
				if(!$this->combat_unit_id)
					$this->combat_unit_id = "character-$this->character_id";
				$this->_combatUnit->get(array('combat_unit_id' => $this->combat_unit_id));
			}
			$this->_combatUnit->combat_unit_id = $this->combat_unit_id;
			$this->_combatUnit->name = $this->_characterName;
			$this->_combatUnit->faction_id = $this->faction_id;
			$this->_combatUnit->health = $this->health;
			$this->_combatUnit->health_max = $this->health_max;
		}
		return $this->_combatUnit;
	}


	//calculates "real" level from level & health
	public function getEffectiveLevel($addFaction)
	{
		$factionMult = 0.1; //magic number!
		$level = $this->level;
		if($addFaction)
			$level *= 1 + $factionMult * $this->rank_id;
		if($this->health_max)
			$level = round($level * $this->health / $this->health_max);
		return $level;
	}


	//checks mission history for last mission's data
	public function getLastMission($maxProgress)
	{
		$sql = "SELECT m.*, s.name AS service_name
			FROM character_missions m LEFT JOIN services s USING(service_id)
			WHERE character_id=:id AND progress<=:maxProgress
			ORDER BY rollover_id DESC LIMIT 1";
		$params = array('id' => $this->character_id, 'maxProgress' => $maxProgress);
		if($row = $this->_dbClient->selectRow($sql, $params))
		{
			$row['_target'] = $this->getMissionTarget($row['type'], $row['params']);
			$row['_name'] = $this->getMissionName($row['type'], $row['_target']);
			$row['_statusName'] = Daemon_Dictionary::$missionProgress[$row['progress']];
			return $row;
		}
		else return null;
	}


	private function getMissionName($type, $name)
	{
		switch($type)
		{
			case'monster':
				return "pokonaj potwora: $name";
			case'item':
				return "przynieś przedmiot: $name";
			default:
				return null;
		}
	}


	private function getMissionTarget($type, $params)
	{
		switch($type)
		{
			case'monster':
				$sql = "SELECT name FROM monsters WHERE monster_id=:id";
				return $this->_dbClient->selectValue($sql, array('id' => $params));
			case'item':
				$sql = "SELECT name FROM items WHERE item_id=:id";
				return $this->_dbClient->selectValue($sql, array('id' => $params));
			default:
				return null;
		}
	}


	//returns a DbObject_Location instance representing current location
	public function getLocation()
	{
		$object = new Daemon_DbObject_Location();
		$object->attachDbClient($this->_dbClient);
		$object->attachCharacterData($this);
		if($this->character_id)
			$object->get(array('location_id' => $this->location_id));
		return $object;
	}


	public function getLocationEvent()
	{
		return json_decode($this->location_event, true);
	}


	//fetches a list of possible respawn locations
	public function getRespawns($defaultRespawn)
	{
		$sql = "SELECT location_id, name FROM locations WHERE location_id = :defaultRespawn
			OR location_id IN (
				SELECT r.respawn_id FROM regions r
				JOIN character_regions cr ON cr.region_id=r.region_id AND cr.character_id=:characterId
			)
			OR location_id IN (
				SELECT l.location_id FROM character_regions cr
				JOIN locations l ON cr.region_id=l.region_id AND cr.character_id=:characterId
				WHERE l.type='caern' AND l.faction_id=:factionId
			)";
		$params = array('characterId' => $this->character_id, 'defaultRespawn' => $defaultRespawn,
			'factionId' => $this->faction_id);
		if($data = $this->_dbClient->selectAll($sql, $params))
		{
			$result = array();
			foreach($data as $row)
				$result[$row['location_id']] = $row['name'];
			return $result;
		}
		else return array();
	}


	public function getSpells()
	{
		$sql = "SELECT spell_id, name FROM spells ORDER BY name";
		$data = $this->_dbClient->selectAll($sql);
		foreach($data as &$row)
		{
			$col = "sp_$row[spell_id]";
			$row['_cost'] = isset($this->$col) ? $this->$col : null;
			$row['_cast'] = ($row['_cost'] <= $this->mana);
		}
		return $data;
	}


	//increases selected attribute if there is enough xp
	public function improveAttribute($key)
	{
		$col = "a_$key";
		if(isset($this->$col))
		{
			$cost = $this->$col;
			if($cost <= $this->xp_free)
			{
				$this->$col += 1;
				$this->xp_used += $cost;
				$this->xp_free -= $cost;
				$this->resetCombatStats();
				$this->put();
			}
			else Daemon_MsgQueue::add('Nie masz dość doświadczenia.');
		}
		else Daemon_MsgQueue::add('Wybrana cecha nie istnieje.');
	}


	//increases faction reputation & rank
	public function improveReputation($factionId, $delta)
	{
		//join faction if needed
		if(!$this->faction_id)
		{
			$sql = "SELECT name FROM factions WHERE faction_id=:id";
			$factionName = $this->_dbClient->selectValue($sql, array('id' => $factionId));
			$this->faction_id = $factionId;
			Daemon_MsgQueue::add("Dołączasz do frakcji: $factionName.");
		}
		//raise reputation
		$this->faction_points += $delta;
		//check for new rank
		$sql = "SELECT r.rank_id, r.title_id, t.name_$this->_gender AS title_name
			FROM faction_ranks r LEFT JOIN titles t USING(title_id)
			WHERE faction_id=:id
			AND rank_id = (SELECT MAX(rank_id) FROM faction_ranks WHERE faction_id=:id AND min_points <= :points)";
		$params = array('id' => $factionId, 'points' => $this->faction_points);
		$newRank = $this->_dbClient->selectRow($sql, $params);
		if($newRank && ($newRank['rank_id'] > (int) $this->rank_id))
		{
			$this->rank_id = $newRank['rank_id'];
			if($newRank['title_id'])
				Daemon_MsgQueue::add("Zdobywasz nową rangę: $newRank[title_name] (poziom $newRank[rank_id]).");
			else Daemon_MsgQueue::add("Zdobywasz nową rangę (poziom $newRank[rank_id]).");
		}
		else Daemon_MsgQueue::add("Rośnie twoja reputacja we frakcji.");
	}


	//increases selected skill if there is enough xp
	public function improveSkill($key)
	{
		$col = "s_$key";
		if(isset($this->$col))
		{
			$cost = $this->$col;
			if($cost > 0)
			{
				if($cost <= $this->xp_free)
				{
					$this->$col += 1;
					$this->xp_used += $cost;
					$this->xp_free -= $cost;
					$this->resetCombatStats();
					$this->put();
				}
				else Daemon_MsgQueue::add('Nie masz dość doświadczenia.');
			}
			else Daemon_MsgQueue::add('Nie znasz jeszcze tej umiejętności.');
		}
		else Daemon_MsgQueue::add('Wybrana umiejętność nie istnieje.');
	}


	//updates combat stats with equipment bonuses
	private function loadEquipmentBonuses()
	{
		//prepare variables
		$unit = $this->getCombatUnit();
		$inventory = new Daemon_Inventory($this->_dbClient, $this);
		$zweihander = false;
		$attackBonusKeys = array(
			'pstr_p', 'pstr_c', 'patk_p', 'patk_c',
			'mstr_p', 'mstr_c', 'matk_p', 'matk_c',
		);
		$modWpn1 = array_fill_keys($attackBonusKeys, 0);
		$modWpn2 = array_fill_keys($attackBonusKeys, 0);
		$armorBonusKeys = array(
			'pdef_p', 'pdef_c', 'pres_p', 'pres_c',
			'mdef_p', 'mdef_c', 'mres_p', 'mres_c',
			'armor', 'speed', 'regen',
		);
		$modArmor = array_fill_keys($armorBonusKeys, 0);
		//reset hands & armor
		$unit->type1 = 'p';
		$unit->count1 = 1;
		$unit->sp1_type = null;
		$unit->sp1_param = null;
		$unit->type2 = 'p';
		$unit->count2 = 1;
		$unit->sp2_type = null;
		$unit->sp2_param = null;
		$unit->armor_sp_type = null;
		$unit->armor_sp_param = null;
		//find equipped items, calculate equipment bonuses
		$sql = "SELECT item_id, equipped FROM inventory WHERE character_id=:id AND equipped IS NOT NULL";
		$params = array('id' => $this->character_id);
		foreach($this->_dbClient->selectAll($sql, $params) AS $row)
		{
			$item = new Daemon_DbObject_Item();
			$item->attachDbClient($this->_dbClient);
			$item->get(array('item_id' => $row['item_id']));
			switch($row['equipped'])
			{
				case'hand_a':
					$zweihander = ('weapon2h' == $item->type);
					$unit->count1 = 1;
					$unit->type1 = $item->damage_type;
					$unit->sp1_type = $item->special_type;
					$unit->sp1_param = $item->special_param;
					foreach($attackBonusKeys as $name)
						$modWpn1[$name] += $item->$name;
					break;
				case'hand_b':
					$unit->count2 = 1;
					$unit->type2 = $item->damage_type;
					$unit->sp2_type = $item->special_type;
					$unit->sp2_param = $item->special_param;
					foreach($attackBonusKeys as $name)
						$modWpn2[$name] += $item->$name;
					break;
				case'armor':
					$unit->armor_sp_type = $item->special_type;
					$unit->armor_sp_param = $item->special_param;
					//nobreak
				default:
					foreach($attackBonusKeys as $name)
					{
						$modWpn1[$name] += $item->$name;
						$modWpn2[$name] += $item->$name;
					}
					break;
			}
			foreach($armorBonusKeys as $name)
				$modArmor[$name] += $item->$name;
		}
		$unit->count1 = 1;
		$unit->count2 = $zweihander ? 0 : 1;
		//first hand
		$pstrSkill = $this->s_pstr;
		$mstrSkill = $this->s_mstr;
		if($zweihander)
		{
			$pstrSkill *= 1.7;
			$mstrSkill *= 1.7;
		}
		if('m' != $unit->type1)
		{
			$unit->atk1 = Daemon_Math::combatStat($this->a_dex, $modWpn1['patk_p'], $modWpn1['patk_c'], $this->s_patk);
			$unit->str1 = Daemon_Math::combatStat($this->a_str, $modWpn1['pstr_p'], $modWpn1['pstr_c'], $pstrSkill);
		}
		else
		{
			$unit->atk1 = Daemon_Math::combatStat($this->a_pwr, $modWpn1['matk_p'], $modWpn1['matk_c'], $this->s_matk);
			$unit->str1 = Daemon_Math::combatStat($this->a_pwr, $modWpn1['mstr_p'], $modWpn1['mstr_c'], $mstrSkill);
		}
		//second hand
		if('m' != $unit->type2)
		{
			$unit->atk2 = Daemon_Math::combatStat($this->a_dex, $modWpn2['patk_p'], $modWpn2['patk_c'], $this->s_patk);
			$unit->str2 = Daemon_Math::combatStat($this->a_str, $modWpn2['pstr_p'], $modWpn2['pstr_c'], $this->s_pstr);
		}
		else
		{
			$unit->atk2 = Daemon_Math::combatStat($this->a_pwr, $modWpn2['matk_p'], $modWpn2['matk_c'], $this->s_matk);
			$unit->str2 = Daemon_Math::combatStat($this->a_pwr, $modWpn2['mstr_p'], $modWpn2['mstr_c'], $this->s_mstr);
		}
		//physical defense
		$unit->pdef = Daemon_Math::combatStat($this->a_dex, $modArmor['pdef_p'], $modArmor['pdef_c'], $this->s_pdef);
		$unit->pres = Daemon_Math::combatStat($this->a_vit, $modArmor['pres_p'], $modArmor['pres_c'], $this->s_pres);
		//magical defense
		$unit->mdef = Daemon_Math::combatStat($this->a_wil, $modArmor['mdef_p'], $modArmor['mdef_c'], $this->s_mdef);
		$unit->mres = Daemon_Math::combatStat($this->a_wil, $modArmor['mres_p'], $modArmor['mres_c'], $this->s_mres);
		//armor & speed
		$unit->speed = Daemon_Math::combatStat($this->a_dex, $modArmor['speed'], 0, 0);
		$unit->armor = $modArmor['armor'];
		//regen
		$unit->regen = Daemon_Math::combatRegen($this->a_vit, $modArmor['regen'] + $this->s_preg);
		$unit->put();
	}


	//try to subtract the cost from character's gold, cancel the operation if the cost is too big
	public function payGold($cost, $bankEnabled)
	{
		$charGold = $this->gold_purse;
		if($bankEnabled)
			$charGold += $this->gold_bank;
		if($charGold < $cost)
		{
			Daemon_MsgQueue::add("Wymagane $cost zł - nie masz tyle złota.");
			return false;
		}
		$deltaPurse = min($cost, $this->gold_purse);
		$this->gold_purse -= $deltaPurse;
		$this->gold_bank -= $cost - $deltaPurse;
		$this->put();
		return true;
	}


	//regenerates one-turn-worth of health & mana
	public function regen($resting)
	{
		$this->mana += $this->mana_regen;
		if($resting)
		{
			$this->health += $this->a_vit;
			$this->mana += ceil($this->a_pwr / 4);
		}
		if($this->health > $this->health_max)
			$this->health = $this->health_max;
		if($this->mana > $this->mana_max)
			$this->mana = $this->mana_max;
	}


	//calculates combat stats based on attributes, skills and equipment
	public function resetCombatStats()
	{
		//health
		$old = $this->health_max;
		$this->health_max = 3*$this->a_str + 7*$this->a_vit;
		$this->health += $this->health_max - $old;
		//mana
		$old = $this->mana_max;
		$this->mana_max = 3*$this->a_pwr + 2*$this->a_wil;
		$this->mana += $this->mana_max - $old;
		$this->mana_regen = Daemon_Math::manaRegen($this->a_wil, $this->s_mreg);
		//used xp
		$this->xp_used = 0;
		foreach(array_keys(Daemon_Dictionary::$characterAttributes) as $key)
		{
			$col = "a_$key";
			$this->xp_used += $this->$col * ($this->$col + 1) / 2;
		}
		foreach(array_keys(Daemon_Dictionary::$characterSkills) as $key)
		{
			$col = "s_$key";
			$this->xp_used += $this->$col * ($this->$col + 1) / 2;
		}
		//update combat stats
		$this->loadEquipmentBonuses();
	}


	//respawns in selected location
	public function respawn($locationId, $defaultRespawn)
	{
		$respawns = $this->getRespawns($defaultRespawn);
		if(!isset($respawns[$locationId]))
		{
			Daemon_MsgQueue::add('Wybrana lokacja nie jest dostępna.');
			return false;
		}
		$this->location_id = $locationId;
		$this->health = $this->health_max;
		$this->mana = 4 * $this->mana_regen;
		$this->resetCombatStats();
		$this->put();
		return true;
	}


	//executes selected event, returns event log
	public function runEvent(Daemon_View $view)
	{
		$event = $this->getLocationEvent();
		//monster attack
		if(isset($event['monsterId']))
		{
			$combat = new Daemon_MonsterCombat();
			$combat->attachCharacterData($this);
			$combat->attachDbClient($this->_dbClient);
			$combat->execute($view, $event['monsterId']);
			//return log
			return $combat->getCombatLog();
		}
		//special event
		if(isset($event['eventId'], $event['params']))
		{
			$handler = new Daemon_Event();
			$handler->attachCharacterData($this);
			$handler->attachDbClient($this->_dbClient);
			$handler->execute($view, $event['eventId'], $event['params']);
			return $handler->getEventLog();
		}
		//no event
		return null;
	}


	//updates character's health, location etc to represent death
	public function setDeath($clearEquipment)
	{
		$this->deaths += 1;
		$this->health = 0;
		$this->mana = 0;
		$this->xp_free = 0;
		$this->location_id = null;
		if($clearEquipment)
		{
			$this->gold_purse = 0;
			$sql = "DELETE FROM inventory WHERE character_id=:id
				AND status='inventory' AND NOT FIND_IN_SET('bound', flags)";
			$params = array('id' => $this->character_id);
			$this->_dbClient->query($sql, $params);
		}
		$this->resetCombatStats();
	}


	public function setLocationEvent(array $data)
	{
		$event = array();
		//monster attack
		if(isset($data['monsterId']))
			$event['monsterId'] = $data['monsterId'];
		//special event
		if(isset($data['eventId'], $data['params']))
		{
			$event['eventId'] = $data['eventId'];
			$event['params'] = $data['params'];
		}
		$this->location_event = json_encode($event);
	}
}
