<?php
//@author Krzysztof Sikorski
$cfg = require_once'./_init.php';


class Daemon_Controller_Inventory extends Daemon_Controller
{
	protected $pageSubtitle = 'Ekwipunek';
	protected $pageTemplatePath = 'inventory.xml';
	protected $requireActiveChar = true;
	protected $requireAuthentication = true;
	protected $requireNoEvents = true;
	private $inventory;
	private $eventLog;


	public function prepareModel()
	{
		$this->inventory = new Daemon_Inventory($this->dbClient, $this->characterData);
	}


	public function prepareView()
	{
		if($this->eventLog)
		{
			$this->pageSubtitle = 'Użycie przedmiotu';
			$this->view->eventLog = $this->eventLog;
		}
		else $this->view->eventLog = null;
		$items = $this->inventory->getItems('inventory');
		$this->view->equipmentSlots = Daemon_Dictionary::$equipmentSlots;
		//prepare items
		$cmdNames = Daemon_Dictionary::$equipmentButtons;
		foreach($items as &$row)
		{
			if($row['equipped'])
				$cmd = 'unequip';
			elseif('item' != $row['item']->type)
				$cmd = 'equip';
			else $cmd = 'use';
			if(isset($cmdNames[$cmd]))
			{
				$row['item']->_cmdType = $cmd;
				$row['item']->_cmdName = $cmdNames[$cmd];
			}
			else
			{
				$row['item']->_cmdType = null;
				$row['item']->_cmdName = null;
			}
			$row['_showSlots'] = ('equip' == $cmd);
			$row['_slots'] = $row['item']->getSlots();
			$row['_multiSlots'] = (count($row['_slots']) > 1);
		}
		$this->view->items = $this->inventory->groupItemsByType($items);
		$this->view->equipment = $this->inventory->getEquipment($items);
		//prepare combat stats
		$unit = (array) $this->characterData->getCombatUnit();
		$attackTypes = Daemon_Dictionary::$combatAttackTypes;
		$attackSpecials = Daemon_Dictionary::$combatAttackSpecials;
		$armorSpecials = Daemon_Dictionary::$combatArmorSpecials;
		$unit['type1_name'] = $unit['type1'] ? $attackTypes[$unit['type1']] : null;
		$unit['type2_name'] = $unit['type2'] ? $attackTypes[$unit['type2']] : null;
		$unit['sp1_name'] = $unit['sp1_type'] ? $attackSpecials[$unit['sp1_type']] : null;
		$unit['sp2_name'] = $unit['sp2_type'] ? $attackSpecials[$unit['sp2_type']] : null;
		$unit['armor_sp_name'] = $unit['armor_sp_type'] ? $armorSpecials[$unit['armor_sp_type']] : null;
		$this->view->combatStats = $unit;
	}


	protected function runCommands()
	{
		//equip item
		if(isset($_POST['equip'], $_POST['slot']))
		{
			$this->inventory->equip($_POST['equip'], $_POST['slot']);
			$this->characterData->resetCombatStats();
			$this->characterData->put();
			return true;
		}
		//unequip item
		if(isset($_POST['unequip']))
		{
			$this->inventory->unequip($_POST['unequip']);
			$this->characterData->resetCombatStats();
			$this->characterData->put();
			return true;
		}
		//use item
		if(isset($_POST['use']))
		{
			$handler = new Daemon_Item();
			$handler->attachCharacterData($this->characterData);
			$handler->attachDbClient($this->dbClient);
			$handler->execute($this->view, $_POST['use']);
			$this->eventLog = $handler->getUsageLog();
			return true;
		}
		return false;
	}
}


$ctrl = new Daemon_Controller_Inventory($cfg);
$ctrl->execute();
