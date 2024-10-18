<?php
//@author Krzysztof Sikorski
$cfg = require_once'../_init.php';


class Daemon_Controller_Page extends Daemon_Scyzoryk_Controller
{
	protected $pageSubtitle = 'Potwory';
	protected $pageTemplatePath = 'scyzoryk/monster-edit.xml';
	private $monster;


	protected function prepareModel()
	{
		$this->monster = new Daemon_DbObject_Monster();
		$this->monster->attachDbClient($this->dbClient);
		if($this->editId)
			$this->monster->get(array('monster_id' => $this->editId));
	}


	protected function prepareView()
	{
		$this->pageSubtitleDetails = $this->monster ? $this->monster->name : null;
		$this->view->monster = $this->monster;
		$this->view->drops = $this->browser->getMonsterDrops($this->editId);
		$this->view->titles = $this->browser->getTitles();
		$this->view->combatUnits = $this->browser->getCombatUnits(null);
		$names = Daemon_Dictionary::$monsterClasses;
		$this->view->className = isset($names[$this->monster->class]) ? $names[$this->monster->class] : null;
		$this->view->attackTypes = Daemon_Dictionary::$combatAttackTypes;
		$this->view->attackSpecials = Daemon_Dictionary::$combatAttackSpecials;
		$this->view->armorSpecials = Daemon_Dictionary::$combatArmorSpecials;
	}


	protected function runCommands()
	{
		if(isset($_POST['monster_id']))
		{
			if(!$_POST['monster_id'])
			{
				Daemon_MsgQueue::add('Uzupełnij ID.');
				return true;
			}
			$this->monster->import($_POST);
			$this->monster->put();
			return true;
		}
		//add drop
		if(isset($_POST['addDrop'], $_POST['id']) && $_POST['id'])
		{
			$params = array('monster_id' => $this->editId, 'item_id' => $_POST['id']);
			$row = new Daemon_Scyzoryk_DbRowMonsterDrop($params);
			$this->editor->updateRow($row);
			return true;
		}
		//delete drops
		if(isset($_POST['del']))
		{
			$this->editor->deleteMonsterDrops($this->editId, $_POST['del']);
			return true;
		}
		return false;
	}
}


$ctrl = new Daemon_Controller_Page($cfg);
$ctrl->execute();
