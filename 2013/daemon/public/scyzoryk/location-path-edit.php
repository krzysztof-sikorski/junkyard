<?php
//@author Krzysztof Sikorski
$cfg = require_once'../_init.php';


class Daemon_Scyzoryk_Controller_LocationPathEdit extends Daemon_Scyzoryk_Controller
{
	protected $pageSubtitle = 'Lokacje';
	protected $pageTemplatePath = 'scyzoryk/location-path-edit.xml';
	private $path;


	protected function prepareModel()
	{
		$this->path = $this->editor->selectRow('Daemon_Scyzoryk_DbRowLocationPath', $this->editId, $this->editId2);
	}


	protected function prepareView()
	{
		$this->pageSubtitleDetails = $this->path ? 'edycja ścieżki' : null;
		$this->view->path = $this->path;
	}


	protected function runCommands()
	{
		if(is_null($this->path))
			return false;
		if(isset($_POST['name']))
		{
			$this->path->name = $_POST['name'];
			$this->path->cost_gold = Daemon::getArrayValue($_POST, 'cost_gold');
			$this->path->cost_mana = Daemon::getArrayValue($_POST, 'cost_mana');
			$this->editor->updateRow($this->path);
			return true;
		}
		return false;
	}
}


$ctrl = new Daemon_Scyzoryk_Controller_LocationPathEdit($cfg);
$ctrl->execute();
