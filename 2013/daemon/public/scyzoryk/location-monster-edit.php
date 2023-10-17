<?php
//@author Krzysztof Sikorski
$cfg = require_once'../_init.php';


class Daemon_Scyzoryk_Controller_LocationMonsterEdit extends Daemon_Scyzoryk_Controller
{
	protected $pageSubtitle = 'Lokacje';
	protected $pageTemplatePath = 'scyzoryk/location-monster-edit.xml';
	private $monster;


	protected function prepareModel()
	{
		$this->monster = $this->editor->selectRow('Daemon_Scyzoryk_DbRowLocationMonster', $this->editId, $this->editId2);
	}


	protected function prepareView()
	{
		$this->pageSubtitleDetails = $this->monster ? 'edycja zdarzenia-potwora' : null;
		$this->view->monster = $this->monster;
	}


	protected function runCommands()
	{
		if(is_null($this->monster))
			return false;
		if(isset($_POST['chance']))
		{
			$this->monster->chance = $_POST['chance'];
			$this->editor->updateRow($this->monster);
			return true;
		}
		return false;
	}
}


$ctrl = new Daemon_Scyzoryk_Controller_LocationMonsterEdit($cfg);
$ctrl->execute();
