<?php
//@author Krzysztof Sikorski
$cfg = require_once'../_init.php';


class Daemon_Controller_Page extends Daemon_Scyzoryk_Controller
{
	protected $pageSubtitle = 'Jednostki bojowe';
	protected $pageTemplatePath = 'scyzoryk/combat-units.xml';
	private $filter;


	protected function prepareModel()
	{
		$this->filter = new Daemon_Scyzoryk_Filter('combat-units');
	}


	protected function prepareView()
	{
		$this->view->filter = $this->filter;
		$units = array();
		foreach ($this->browser->getCombatUnits($this->filter) as $row)
			if (!$row['_character'])
				$units[] = $row;
		$this->view->units = $units;
	}


	protected function runCommands()
	{
		//add new row
		if(isset($_POST['newId'], $_POST['newName']) && $_POST['newId'])
		{
			$object = new Daemon_DbObject_CombatUnit();
			$object->attachDbClient($this->dbClient);
			$object->combat_unit_id = $_POST['newId'];
			$object->name = $_POST['newName'];
			$object->put();
			return true;
		}
		//delete rows
		if(isset($_POST['del']))
		{
			$this->editor->deleteCombatUnits($_POST['del']);
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


$ctrl = new Daemon_Controller_Page($cfg);
$ctrl->execute();
