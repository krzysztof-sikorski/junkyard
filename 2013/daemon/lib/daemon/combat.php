<?php
//@author Krzysztof Sikorski
//combat engine
//usage:
//$combat = new Daemon_Combat();
//$logger = new Daemon_Combat_Log();
//$combat->attachLogger($logger);
//$combat->addUnit('a', $unit1, true);
//$combat->addUnit('b', $unit2, false);
//$combat->execute();
//$combatLog = (string) $logger;
class Daemon_Combat
{
	public $units = array();
	public $sideA = array();
	public $sideB = array();
	public $round = 0;
	public $roundLimit = 120;
	public $tickLimit = 1;
	private $_logger = null;


	public function addUnit($unitId, Daemon_Combat_Unit $unit, $attacker)
	{
		$unit->attachLogger($this->_logger);
		$unit->_id = $unitId;
		$unit->_ticks = 0;
		$unit->_initiative = mt_rand(0, 32 * $unit->speed);
		$unit->_target = null;
		$unit->_threat = array();
		$unit->_dmgDealt = 0;
		$unit->_dmgTaken = 0;
		$this->units[$unitId] = $unit;
		$unit->_attacker = (bool) $attacker;
		if($unit->_attacker)
		{
			$this->sideA[$unitId] = $unit;
			$unit->_allies = &$this->sideA;
			$unit->_enemies = &$this->sideB;
		}
		else
		{
			$this->sideB[$unitId] = $unit;
			$unit->_allies = &$this->sideB;
			$unit->_enemies = &$this->sideA;
		}
	}


	public function attachLogger(Daemon_Combat_Log $logger)
	{
		$this->_logger = $logger;
		foreach($this->units as $unit)
			$unit->attachLogger($logger);
	}


	protected function callbackActive($unit)
	{
		return $unit->_ticks > $this->tickLimit;
	}


	protected function callbackSpeedSum($prev, $unit)
	{
		return $prev + $unit->speed;
	}


	protected function callbackTickCompare($unit1, $unit2)
	{
		if($unit1->_ticks == $unit2->_ticks)
			return $unit1->_initiative - $unit2->_initiative;
		else return ($unit1->_ticks < $unit2->_ticks) ? -1 : +1;
	}


	protected function callbackTickInc($unit, $key)
	{
		if($unit->health > 0)
			$unit->_ticks += $unit->speed;
		else $unit->_ticks = null;
	}


	protected function debug($round, array $units)
	{
		if($this->_logger)
		{
			$result = array("Segment $round");
			foreach($units as $unit)
				$result[] = (string) $unit;
			$this->_logger->add($result);
		}
	}


	public function execute($noCleanup = false)
	{
		if(!$this->units)
			return;
		$unitCount = count($this->units);
		if ($this->_logger)
			$this->_logger->groupCombat = ($unitCount > 2);
		$round = 0;
		$roundLimit = 100 + 10 * $unitCount;
		$speedSum = array_reduce($this->units, array($this, 'callbackSpeedSum'), 0);
		$this->tickLimit = 1 + ceil(2 * $speedSum / count($this->units));
		while($round < $roundLimit)
		{
			$victory = false;
			array_walk($this->units, array($this, 'callbackTickInc'));
			while($actor = $this->getActiveUnit())
			{
				++$round;
				$actor->_ticks -= $this->tickLimit;
				$actor->executeRound($round);
				//victory check, sides should be updated by units
				$victory = (!count($this->sideA) || !count($this->sideB));
				if($victory)
					break;
			}
			if($victory)
				break;
		}
		//after-combat regen & fixes
		if (!$noCleanup)
		{
			foreach($this->units as $unit)
			{
				if ($unit->health < 1)
				{
					$unit->health = 0;
				}
				elseif (($unit->regen > 0) && ($unit->health < $unit->health_max))
				{
					$unit->health = $unit->health_max;
					if($this->_logger)
						$this->_logger->add("<i>$unit->name</i> regeneruje pełnię zdrowia.<br>");
				}
				else
				{
					$unit->health = Daemon_Math::round($unit->health);
				}
			}
		}
	}


	protected function getActiveUnit()
	{
		$active = array_filter($this->units, array($this, 'callbackActive'));
		uasort($active, array($this, 'callbackTickCompare'));
		return array_pop($active);
	}


	public function reset()
	{
		$this->units = array();
		$this->sideA = array();
		$this->sideB = array();
	}
}
