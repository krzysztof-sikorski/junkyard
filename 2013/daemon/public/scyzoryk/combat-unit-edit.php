<?php
//@author Krzysztof Sikorski
$cfg = require_once'../_init.php';


class Daemon_Controller_Page extends Daemon_Scyzoryk_Controller
{
	protected $pageSubtitle = 'Jednostki bojowe';
	protected $pageTemplatePath = 'scyzoryk/combat-unit-edit.xml';
	private $unit;


	protected function prepareModel()
	{
		$this->unit = new Daemon_DbObject_CombatUnit();
		$this->unit->attachDbClient($this->dbClient);
		if($this->editId)
			$this->unit->get(array('combat_unit_id' => $this->editId));
	}


	protected function prepareView()
	{
		$this->pageSubtitleDetails = $this->unit ? $this->unit->name : null;
		$this->view->unit = $this->unit;
		$this->view->attackTypes = Daemon_Dictionary::$combatAttackTypes;
		$this->view->attackSpecials = Daemon_Dictionary::$combatAttackSpecials;
		$this->view->armorSpecials = Daemon_Dictionary::$combatArmorSpecials;
	}


	protected function runCommands()
	{
		if(isset($_POST['combat_unit_id']))
		{
			if(!$_POST['combat_unit_id'])
			{
				Daemon_MsgQueue::add('Uzupełnij ID.');
				return true;
			}
			$this->unit->import($_POST);
			$this->unit->put();
			return true;
		}
		return false;
	}
}


$ctrl = new Daemon_Controller_Page($cfg);
$ctrl->execute();
