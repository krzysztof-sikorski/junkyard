<?php
//@author Krzysztof Sikorski
$cfg = require_once'../_init.php';


class Daemon_Scyzoryk_Controller_RegionEdit extends Daemon_Scyzoryk_Controller
{
	protected $pageSubtitle = 'Regiony';
	protected $pageTemplatePath = 'scyzoryk/region-edit.xml';
	private $region;


	protected function prepareModel()
	{
		$this->region = $this->editor->selectRow('Daemon_Scyzoryk_DbRowRegion', $this->editId);
	}


	protected function prepareView()
	{
		$filter = new Daemon_Scyzoryk_Filter('locations', array('region_id'), true);
		$filter->region_id = $this->region->region_id;
		$this->pageSubtitleDetails = $this->region ? $this->region->name : null;
		$this->view->region = $this->region;
		$this->view->locations = $this->browser->getLocations($filter);
	}


	protected function runCommands()
	{
		if(is_null($this->region))
			return false;
		if(isset($_POST['id'], $_POST['name']) && $_POST['id'])
		{
			$this->region->region_id = $_POST['id'];
			$this->region->name = $_POST['name'];
			$this->region->respawn_id = Daemon::getArrayValue($_POST, 'respawn_id');
			$this->region->picture_url = Daemon::getArrayValue($_POST, 'picture_url');
			$this->editor->updateRow($this->region);
			return true;
		}
		return false;
	}
}


$ctrl = new Daemon_Scyzoryk_Controller_RegionEdit($cfg);
$ctrl->execute();
