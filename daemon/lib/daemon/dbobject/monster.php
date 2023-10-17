<?php
//@author Krzysztof Sikorski
class Daemon_DbObject_Monster extends Daemon_DbObject
{
	protected $_tableName = 'monsters';
	protected $_index = array('monster_id');
	public $monster_id;
	public $name;
	public $class = 1;
	public $level = 1;
	public $gold = 0;
	public $chance1 = 1;
	public $chance2 = 1;
	public $title_id = null;
	public $combat_unit_id = null;
	private $_combatUnit;


	//returns Daemon_DbObject_CombatUnit instance
	public function getCombatUnit($full = false)
	{
		if(!$this->_combatUnit)
		{
			$this->_combatUnit = $full ? new Daemon_Combat_Unit() : new Daemon_DbObject_CombatUnit();
			$this->_combatUnit->attachDbClient($this->_dbClient);
			if($this->combat_unit_id)
				$this->_combatUnit->get(array('combat_unit_id' => $this->combat_unit_id));
			$this->_combatUnit->name = $this->name;
			$this->_combatUnit->faction_id = null;
		}
		return $this->_combatUnit;
	}


	public function validate()
	{
		$this->class = max(0, (int) $this->class);
		$this->level = max(0, (int) $this->level);
		$this->gold = max(0, (int) $this->gold);
		$this->chance1 = max(0, (int) $this->chance1);
		$this->chance2 = max(1, (int) $this->chance2);
		//set class
		foreach (Daemon_Dictionary::$monsterClassLevels as $class => $level)
		{
			if ($this->level > $level)
				$this->class = $class;
		}
	}
}
