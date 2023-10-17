<?php
//@author Krzysztof Sikorski
$cfg = require_once'../_init.php';


class Daemon_Scyzoryk_Controller_Monsters extends Daemon_Scyzoryk_Controller
{
	protected $pageSubtitle = 'Potwory';
	protected $pageTemplatePath = 'scyzoryk/monsters.xml';
	private $filter;


	protected function prepareModel()
	{
		$this->filter = new Daemon_Scyzoryk_Filter('monsters', array('class'));
	}


	protected function prepareView()
	{
		$this->view->filter = $this->filter;
		$this->view->monsters = $this->browser->getMonsters($this->filter);
		$this->view->monsterClasses = Daemon_Dictionary::$monsterClasses;
	}


	protected function runCommands()
	{
		//add new row
		if(isset($_POST['newId'], $_POST['newName']) && $_POST['newId'])
		{
			$object = new Daemon_DbObject_Monster();
			$object->attachDbClient($this->dbClient);
			$object->monster_id = $_POST['newId'];
			$object->name = $_POST['newName'];
			$object->put();
			return true;
		}
		//delete rows
		if(isset($_POST['del']))
		{
			$this->editor->deleteMonsters($_POST['del']);
			return true;
		}
		//set filter
		if(isset($_POST['filter']) && is_array($_POST['filter']))
		{
			foreach($_POST['filter'] as $name => $value)
				$this->filter->$name = $value;
			return true;
		}
		return false;
	}
}


$ctrl = new Daemon_Scyzoryk_Controller_Monsters($cfg);
$ctrl->execute();
