<?php
//@author Krzysztof Sikorski
class Daemon_DbObject_Item extends Daemon_DbObject
{
	protected $_tableName = 'items';
	protected $_index = array('item_id');
	public $item_id;
	public $name;
	public $type = 'item';
	public $value = 1, $suggested_value = 0.0;
	public $damage_type = null;
	public $special_type = null;
	public $special_param = null;
	public $pstr_p = 0, $pstr_c = 0, $patk_p = 0, $patk_c = 0;
	public $pdef_p = 0, $pdef_c = 0, $pres_p = 0, $pres_c = 0;
	public $mstr_p = 0, $mstr_c = 0, $matk_p = 0, $matk_c = 0;
	public $mdef_p = 0, $mdef_c = 0, $mres_p = 0, $mres_c = 0;
	public $armor = 0, $speed = 0, $regen = 0.0;
	public $description = null;


	//generates a readable description
	public function getDescription()
	{
		$itemTypes = Daemon_Dictionary::$itemTypes;
		$itemWeaponTypes = Daemon_Dictionary::$itemWeaponTypes;
		$itemDamageTypes = Daemon_Dictionary::$itemDamageTypes;
		$itemArmorTypes = Daemon_Dictionary::$itemArmorTypes;
		$combatAttackSpecials = Daemon_Dictionary::$combatAttackSpecials;
		$combatArmorSpecials = Daemon_Dictionary::$combatArmorSpecials;
		//item type
		$typeName = $specialName = null;
		if (!empty($itemWeaponTypes[$this->type]))
		{
			if ($this->damage_type && isset($itemDamageTypes[$this->damage_type]))
			{
				$typeName = sprintf('broń %s (obrażenia %s)',
					$itemWeaponTypes[$this->type], $itemDamageTypes[$this->damage_type]);
			}
			else
				$typeName = 'tarcza';
			if(isset($combatAttackSpecials[$this->special_type]))
				$specialName = sprintf('%s (%s)', $combatAttackSpecials[$this->special_type], $this->special_param);
		}
		elseif (!empty($itemArmorTypes[$this->type]))
		{
			$typeName = $itemArmorTypes[$this->type];
			if(isset($combatArmorSpecials[$this->special_type]))
				$specialName = sprintf('%s (%s)', $combatArmorSpecials[$this->special_type], $this->special_param);
		}
		else //usables
		{
			$typeName = 'niezakładalny';
		}
		//stats
		$stats = array();
		if($specialName)
			$stats[] = $specialName;
		if('item' != $this->type)
		{
			if($this->pstr_p || $this->pstr_c)
				$stats[] = $this->getStatDescription('pstr', $this->pstr_p, $this->pstr_c);
			if($this->patk_p || $this->patk_c)
				$stats[] = $this->getStatDescription('patk', $this->patk_p, $this->patk_c);
			if($this->pdef_p || $this->pdef_c)
				$stats[] = $this->getStatDescription('pdef', $this->pdef_p, $this->pdef_c);
			if($this->pres_p || $this->pres_c)
				$stats[] = $this->getStatDescription('pres', $this->pres_p, $this->pres_c);
			if($this->mstr_p || $this->mstr_c)
				$stats[] = $this->getStatDescription('mstr', $this->mstr_p, $this->mstr_c);
			if($this->matk_p || $this->matk_c)
				$stats[] = $this->getStatDescription('matk', $this->matk_p, $this->matk_c);
			if($this->mdef_p || $this->mdef_c)
				$stats[] = $this->getStatDescription('mdef', $this->mdef_p, $this->mdef_c);
			if($this->mres_p || $this->mres_c)
				$stats[] = $this->getStatDescription('mres', $this->mres_p, $this->mres_c);
			if($this->armor)
				$stats[] = sprintf('pancerz%+d', $this->armor);
			if($this->speed)
				$stats[] = sprintf('szybkość%+d%%', $this->speed);
			if($this->regen)
				$stats[] = sprintf('regen%+d', $this->regen);
		}
		//final concatenation
		if($stats)
			$desc = sprintf('%s; %s', $typeName, implode(', ', $stats));
		else $desc = $typeName;
		return $desc;
	}


	//returns a list of matching equipment slots
	public function getSlots()
	{
		if ($this->type == 'weapon1h')
			return array('hand_a', 'hand_b');
		elseif ($this->type == 'weapon2h')
			return array('hand_a');
		elseif ($this->type == 'accesory')
			return array('accesory_a', 'accesory_b');
		elseif ($this->type != 'item')
			return array($this->type);
		else return array();
	}


	private function getStatDescription($name, $value_p = 0, $value_c = 0)
	{
		$result = $name;
		if($value_p)
			$result .= sprintf('%+d%%', $value_p);
		if($value_c)
			$result .= sprintf('%+d', $value_c);
		return $result;
	}


	public function updateSuggestedValue(Daemon_DbConfig $dbCfg)
	{
		if ($this->type == 'item')
			return;
		$this->suggested_value = 0.0;
		$baseValue = $dbCfg->generatorBaseValue;
		$weights = $dbCfg->getGeneratorWeights($this->type);
		foreach ($weights as $key => $val)
		{
			if (isset($this->$key))
				$this->suggested_value += $this->$key * $val;
		}
		$this->suggested_value *= $baseValue;
	}


	public function validate()
	{
		if ($this->type == 'weapon1h' || $this->type == 'weapon2h')
		{
			$specials = Daemon_Dictionary::$combatAttackSpecials;
			if(!isset($specials[$this->special_type]))
			{
				$this->special_type = null;
				$this->special_param = null;
			}
		}
		elseif ($this->type != 'item')
		{
			$this->damage_type = null;
			$specials = Daemon_Dictionary::$combatArmorSpecials;
			if(($this->type != 'armor') || !isset($specials[$this->special_type]))
			{
				$this->special_type = null;
				$this->special_param = null;
			}
		}
		else //usables
		{
			$this->damage_type = null;
		}
	}
}
