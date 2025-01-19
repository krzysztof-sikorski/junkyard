<?php
//@author Krzysztof Sikorski
class Daemon_Duel
{
	private $dbClient = null;
	private $characterData = null;
	private $combatLog;
	const WINNER_ACTOR = 'a';
	const WINNER_TARGET = 'b';


	public function attachCharacterData(Daemon_DbObject_CharacterData $characterData)
	{
		$this->characterData = $characterData;
	}


	public function attachDbClient(Daemon_DbClient $dbClient)
	{
		$this->dbClient = $dbClient;
	}


	public function getCombatLog()
	{
		return $this->combatLog;
	}


	//returns target character's object
	private function getTarget($targetId)
	{
		$params = array('character_id' => $targetId);
		$cdata = new Daemon_DbObject_CharacterData();
		$cdata->attachDbClient($this->dbClient);
		$cdata->get($params);
		$sql = "SELECT name FROM characters WHERE character_id=:character_id";
		$cdata->_characterName = $this->dbClient->selectValue($sql, $params);
		return $cdata;
	}


	public function execute(Daemon_View $view, $locationType, $targetId)
	{
		$levelMultiplier = 1.2; //magic number!
		$attacker = $this->characterData;
		$defender = $this->getTarget($targetId);
		$winner = null;
		$winnerName = null;
		$winnerXp = 0;
		$winnerLevel = null;
		$loserName = null;
		$loserLevel = 0;
		$sql = "SELECT MAX(rollover_id) FROM rollovers";
		$rolloverId = $this->dbClient->selectValue($sql);
		//check if combat is possibile
		if(!$attacker->canAttack((array) $defender, $rolloverId, true))
			return null;
		//check combat type
		$combatType = $attacker->getCombatType((array) $defender, $locationType);
		if(!$combatType)
		{
			Daemon_MsgQueue::add('Nie możesz zaatakować tej postaci.');
			return false;
		}
		$arena = ('arena' == $combatType);
		//pre-calculate xp rewards (depends on health, which changes in combat)
		$factionBonus = ($attacker->faction_id && $defender->faction_id
			&& ($attacker->faction_id != $defender->faction_id));
		$attackerLevel = $attacker->getEffectiveLevel($factionBonus);
		$defenderLevel = $defender->getEffectiveLevel($factionBonus);
		//execute combat
		$winner = $this->runCombat($defender, $combatType);
		//update winner
		if(self::WINNER_ACTOR == $winner)
		{
			$winnerChar = $attacker;
			$loserChar = $defender;
			$winnerXp = Daemon_Math::round($levelMultiplier * $defenderLevel);
		}
		elseif(self::WINNER_TARGET == $winner)
		{
			$winnerChar = $defender;
			$loserChar = $attacker;
			$winnerXp = Daemon_Math::round($levelMultiplier * $attackerLevel);
		}
		else
		{
			$winnerChar = null;
			$loserChar = null;
		}
		if($winnerChar && $loserChar)
		{
			$winnerName = $winnerChar->_characterName;
			$loserName = $loserChar->_characterName;
			$winnerChar->xp_free += $winnerXp;
			$sql = "UPDATE character_statistics SET duel_wins=duel_wins+1 WHERE character_id=:id";
			$this->dbClient->query($sql, array('id' => $winnerChar->character_id));
			$sql = "UPDATE character_statistics SET duel_losses=duel_losses+1 WHERE character_id=:id";
			$this->dbClient->query($sql, array('id' => $loserChar->character_id));
		}
		//save characters
		$attacker->put();
		$defender->put();
		//log duel
		$sql = "INSERT INTO duels(rollover_id, attacker_id, defender_id, type, winner, combat_log)
			VALUES (:rolloverId, :attackerId, :defenderId, :type, :winner, :combatLog)";
		$params = array('rolloverId' => $rolloverId,
			'attackerId' => $attacker->character_id, 'defenderId' => $defender->character_id,
			'type' => $combatType, 'winner' => $winner, 'combatLog' => $this->combatLog);
		$this->dbClient->query($sql, $params);
		//generate report
		$view->arena = $arena;
		$view->combatLog = $this->combatLog;
		$view->attackerName = $attacker->_characterName;
		$view->defenderName = $defender->_characterName;
		$view->winner = $winner;
		$view->winnerName = $winnerName;
		$view->winnerXp = $winnerXp;
		$view->winnerLevel = $winnerLevel;
		$view->loserName = $loserName;
		ob_start();
		$view->display('duelcombat.xml');
		$this->combatLog = ob_get_clean();
		return true;
	}


	//executes combat, returns winner flag
	private function runCombat(Daemon_DbObject_CharacterData $target, $combatType)
	{
		$combat = new Daemon_Combat();
		$logger = new Daemon_Combat_Log();
		$combat->attachLogger($logger);
		//prepare units
		$attackerUnit = $this->characterData->getCombatUnit(true);
		$defenderUnit = $target->getCombatUnit(true);
		if('arena' == $combatType)
		{
			$attackerUnit->health = $attackerUnit->health_max;
			$defenderUnit->health = $defenderUnit->health_max;
		}
		//execute combat
		$combat->addUnit('a', $attackerUnit, true);
		$combat->addUnit('b', $defenderUnit, false);
		$combat->execute();
		$this->combatLog = (string) $logger;
		//check deaths
		$deathA = ($attackerUnit->health < 1);
		$deathB = ($defenderUnit->health < 1);
		//update characters health, gold & equipment, return winner
		if('arena' != $combatType)
		{
			$attackerUnit->put();
			$this->characterData->health = floor($attackerUnit->health);
			$defenderUnit->put();
			$target->health = floor($defenderUnit->health);
		}
		if($deathA && $deathB)
		{
			if('arena' != $combatType)
			{
				$this->characterData->setDeath(true);
				$target->setDeath(true, null);
			}
			return null;//double KO
		}
		elseif($deathA && !$deathB)
		{
			if('arena' != $combatType)
				$this->characterData->setDeath(true);
			return self::WINNER_TARGET;
		}
		elseif(!$deathA && $deathB)
		{
			if('arena' != $combatType)
				$target->setDeath(true);
			return self::WINNER_ACTOR;
		}
		else return null;//draw
	}
}
