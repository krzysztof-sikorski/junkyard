<?php
//@author Krzysztof Sikorski
//combat stats (daemon or monster)
class Daemon_Combat_Unit extends Daemon_DbObject_CombatUnit
{
	private $_logger = null;
	//combat variables
	public $_id = null;
	public $_flawless = true;
	public $_ticks = 0;
	public $_initiative = 0;
	public $_poison = 0;
	public $_attacker = null; //attacker or defender
	public $_target = null; //current target
	public $_threat = array(); //current threat
	public $_allies = array(); //global sideA/sideB list
	public $_enemies = array(); //global sideB/sideA list
	public $_cntDealt = 0;
	public $_dmgDealt = 0;
	public $_cntTaken = 0;
	public $_dmgTaken = 0;


	public function __toString()
	{
		return "[id: $this->_id, name: $this->name, ticks: $this->_ticks, health: $this->health/$this->health_max]";
	}


	public function attachLogger(Daemon_Combat_Log $logger = null)
	{
		$this->_logger = $logger;
	}


	protected function calculateAttack(array $params)
	{
		//attack count - check for swarm
		if(self::SP_SWARM == $params['sp_type'])
			$attackCount = ceil($params['count'] * $this->health / $this->health_max);
		else $attackCount = $params['count'];
		//prepare variables
		$magical = ('m' == $params['type']);
		if($magical)
		{
			$targetDef = $this->_target->mdef;
			$targetRes = $this->_target->mres;
			$armor = 0;
		}
		else
		{
			$targetDef = $this->_target->pdef;
			$targetRes = $this->_target->pres;
			$armor = $this->_target->armor;
			if(self::SP_ETHER == $params['sp_type'])
				$armor *= 1 - $params['sp_param']/100;
		}
		//calculate basic data
		$toHit = 100 * $params['atk'] / ($params['atk'] + $targetDef);
		$baseDmg = $params['str'] * $params['str'] / ($params['str'] + $targetRes);
		//faction effects
		if($this->faction_id && $this->_target->faction_id && ($this->faction_id != $this->_target->faction_id))
		{
			if(self::SP_FACTION == $params['sp_type'])
				$baseDmg *= 1 + $params['sp_param']/100;
			if(self::SP_FACTION == $this->_target->armor_sp_type)
				$baseDmg /= 1 + $this->_target->armor_sp_param/100;
		}
		//execute attacks
		for($i=0; $i < $attackCount; $i++)
		{
			$d100 = mt_rand(0,99);
			//check for demon
			$demon = $magical && (self::SP_DEMON == $this->_target->armor_sp_type);
			if($demon && mt_rand(0,99) < $this->_target->armor_sp_param)
			{
				$regen = min($this->_target->health_max - $this->_target->health, $baseDmg * $this->_target->armor_sp_param / 100);
				$this->_target->health += $regen;
				if($this->_logger)
					$this->_logger->txtDemon($regen);
			}
			//check for hit
			elseif($d100 < $toHit)
			{
				//calculate damage
				$critical = $d100>45;
				if($critical)
				{
					$dmgMult = 2;
					if(self::SP_BLOODY == $params['sp_type'])
						$dmgMult += $params['sp_param']/100;
				}
				else $dmgMult = 1 + mt_rand(0,127)/256;
				$dmg = max(0, $baseDmg * $dmgMult - $armor);
				$this->_target->health -= $dmg;
				//update statistics
				$this->_target->_flawless = false;
				$this->_target->_cntTaken += 1;
				$this->_target->_dmgTaken += $dmg;
				$this->_cntDealt += 1;
				$this->_dmgDealt += $dmg;
				//check for poison
				if(self::SP_POISON == $params['sp_type'])
				{
					$poison = $dmg>0 ? $params['sp_param'] : 0;
					if(self::SP_ANTIPOISON == $this->_target->armor_sp_type)
						$poison *= 1 - $this->_target->armor_sp_param / 100;
					$this->_target->_poison += $poison;
				}
				else $poison = 0;
				//check for shock
				if(self::SP_SHOCK == $this->_target->armor_sp_type)
				{
					$shockDmg = $dmg * $this->_target->armor_sp_param / 100;
					$this->health -= $shockDmg;
				}
				else $shockDmg = 0;
				//check for stun
				$stun = $critical && (self::SP_STUN == $params['sp_type']);
				if($stun)
					$this->_target->_ticks = 0;
				//check for vampirism
				if(self::SP_VAMPIRE == $params['sp_type'])
				{
					$vampRegen = min($this->health_max - $this->health, $dmg * $params['sp_param']/100);
					if(self::SP_ANTIVAMP == $this->_target->armor_sp_type)
						$vampRegen *= 1 - $this->_target->armor_sp_param / 100;
					$this->health += $vampRegen;
				}
				else $vampRegen = 0;
				//print info
				if($this->_logger)
				{
					$this->_logger->txtAttackHit($this->name, $dmg, $magical, $critical,
						$poison, $shockDmg, $stun, $vampRegen);
				}
			}
			else
			{
				if($this->_logger)
					$this->_logger->txtAttackMiss($this->name, $magical);
			}
		}
	}


	protected function executeAttacks()
	{
		if(!$this->_target)
			return;
		$this->_target->_threat[$this->_id] = $this;
		if($this->count1 && $this->type1)
		{
			$attackParams = array(
				'str' => $this->str1,
				'atk' => $this->atk1,
				'type' => $this->type1,
				'count' => $this->count1,
				'sp_type' => $this->sp1_type,
				'sp_param' => $this->sp1_param,
			);
			$this->calculateAttack($attackParams);
		}
		if($this->count2 && $this->type2)
		{
			$attackParams = array(
				'str' => $this->str2,
				'atk' => $this->atk2,
				'type' => $this->type2,
				'count' => $this->count2,
				'sp_type' => $this->sp2_type,
				'sp_param' => $this->sp2_param,
			);
			$this->calculateAttack($attackParams);
		}
		//check for target death
		if($this->_target->health <= 0)
		{
			$this->_target->_ticks = null;
			unset($this->_threat[$this->_target->_id]);
			unset($this->_enemies[$this->_target->_id]);
			if($this->_logger)
				$this->_logger->txtDeath($this->_target->name, $this->_flawless ? $this->name : null);
		}
	}


	public function executeRound($round)
	{
		if($this->_logger)
			$this->_logger->txtRoundHeader($round);
		$this->regen();
		$this->pickTarget();
		if($this->_target)
			$this->executeAttacks();
		$this->poison();
		if($this->_logger)
			$this->_logger->txtRoundFooter();
	}


	protected function pickTarget()
	{
		//clear old target
		$this->_target = null;
		//try current threat
		while($this->_threat && !$this->_target)
		{
			$targetId = array_rand($this->_threat);
			$this->_target = $this->_threat[$targetId];
			if($this->_target->health <= 0) //killed by someone else
			{
				$this->_target = null;
				unset($this->_threat[$targetId]);
			}
		}
		//no threat, try other enemies
		if($this->_enemies && !$this->_target)
		{
			$targetId = array_rand($this->_enemies);
			$this->_target = $this->_enemies[$targetId];
		}
		//print message
		if($this->_logger)
		{
			if($this->_target)
				$this->_logger->txtTargetHeader($this->name, $this->_target->name);
			else $this->_logger->txtTargetHeader($this->name, null);
		}
	}


	protected function poison()
	{
		if($this->_poison)
		{
			$dmg = $this->_poison * $this->_poison / ($this->_poison + $this->pres);
			$this->health -= $dmg;
			if($this->_logger)
				$this->_logger->txtPoison($this->name, $dmg);
			if($this->health <= 0)
			{
				unset($this->_allies[$this->_id]);
				if($this->_logger)
					$this->_logger->txtDeath($this->name, false);
			}
		}
	}


	protected function regen()
	{
		if($this->regen && $this->health < $this->health_max)
		{
			$delta = min($this->health_max - $this->health, $this->regen);
			$this->health += $delta;
			if($this->_logger)
				$this->_logger->txtRegen($this->name, $delta);
		}
	}
}
