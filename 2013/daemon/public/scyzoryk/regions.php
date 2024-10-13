<?php
//@author Krzysztof Sikorski
$cfg = require_once'../_init.php';


class Daemon_Scyzoryk_Controller_Regions extends Daemon_Scyzoryk_Controller
{
	protected $pageSubtitle = 'Regiony';
	protected $pageTemplatePath = 'scyzoryk/regions.xml';


	protected function prepareView()
	{
		$this->view->regions = $this->browser->getRegions();
	}


	protected function runCommands()
	{
		//add new row
		if(isset($_POST['newId'], $_POST['newName']) && $_POST['newId'])
		{
			$params = array('region_id' => $_POST['newId'], 'name' => $_POST['newName']);
			$this->editor->updateRow(new Daemon_Scyzoryk_DbRowRegion($params));
			return true;
		}
		//delete rows
		if(isset($_POST['del']))
		{
			$this->editor->deleteRegions($_POST['del']);
			return true;
		}
		return false;
	}
}


$ctrl = new Daemon_Scyzoryk_Controller_Regions($cfg);
$ctrl->execute();
