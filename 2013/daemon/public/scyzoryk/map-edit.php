<?php
//@author Krzysztof Sikorski
$cfg = require_once'../_init.php';


class Daemon_Scyzoryk_Controller_MapEdit extends Daemon_Scyzoryk_Controller
{
	protected $pageSubtitle = 'Mapy';
	protected $pageTemplatePath = 'scyzoryk/map-edit.xml';
	private $map;


	protected function prepareModel()
	{
		$this->map = $this->editor->selectRow('Daemon_Scyzoryk_DbRowMap', $this->editId);
	}


	protected function prepareView()
	{
		$this->pageSubtitleDetails = $this->map ? $this->map->name : null;
		$this->view->map = $this->map;
	}


	protected function runCommands()
	{
		if(is_null($this->map))
			return false;
		if(isset($_POST['id'], $_POST['name']) && $_POST['id'])
		{
			$this->map->map_id = $_POST['id'];
			$this->map->name = $_POST['name'];
			$this->map->url = Daemon::getArrayValue($_POST, 'url');
			$this->map->sort = max(0, Daemon::getArrayValue($_POST, 'sort'));
			$this->editor->updateRow($this->map);
			return true;
		}
		return false;
	}
}


$ctrl = new Daemon_Scyzoryk_Controller_MapEdit($cfg);
$ctrl->execute();
