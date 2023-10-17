<?php
//@author Krzysztof Sikorski
class Daemon_DbObject_CombatUnit extends Daemon_DbObject
{
	protected $_tableName = 'combat_units';
	protected $_index = array('combat_unit_id');
	public $combat_unit_id;
	public $name = null;
	public $faction_id = null;
	public $health = 70;
	public $health_max = 70;
	public $str1 = 7;
	public $atk1 = 7;
	public $type1 = 'p';
	public $count1 = 1;
	public $sp1_type = null;
	public $sp1_param = null;
	public $str2 = 7;
	public $atk2 = 7;
	public $type2 = null;
	public $count2 = 0;
	public $sp2_type = null;
	public $sp2_param = null;
	public $pdef = 7;
	public $pres = 7;
	public $mdef = 7;
	public $mres = 7;
	public $speed = 7;
	public $armor = 0;
	public $armor_sp_type = null;
	public $armor_sp_param = null;
	public $regen = 0;
	//special property types
	const SP_ANTIPOISON = 'antipoison';
	const SP_ANTIVAMP = 'antivamp';
	const SP_BLOODY = 'bloody';
	const SP_DEMON = 'demon';
	const SP_ETHER = 'ether';
	const SP_FACTION = 'faction';
	const SP_POISON = 'poison';
	const SP_SHOCK = 'shock';
	const SP_STUN = 'stun';
	const SP_SWARM = 'swarm';
	const SP_VAMPIRE = 'vampiric';


	public function validate()
	{
		$attackTypes = Daemon_Dictionary::$combatAttackTypes;
		$attackSpecials = Daemon_Dictionary::$combatAttackSpecials;
		$armorSpecials = Daemon_Dictionary::$combatArmorSpecials;
		if (!isset($attackTypes[$this->type1]))
			$this->type1 = null;
		if (!isset($attackTypes[$this->type2]))
			$this->type2 = null;
		if (!isset($attackSpecials[$this->sp1_type]))
			$this->sp1_type = null;
		if (!isset($attackSpecials[$this->sp2_type]))
			$this->sp2_type = null;
		if (!isset($armorSpecials[$this->armor_sp_type]))
			$this->armor_sp_type = null;
	}
}
