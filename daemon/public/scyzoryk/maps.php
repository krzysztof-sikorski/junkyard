<?php
//@author Krzysztof Sikorski
$cfg = require_once'../_init.php';


class Daemon_Scyzoryk_Controller_Maps extends Daemon_Scyzoryk_Controller
{
	protected $pageSubtitle = 'Mapy';
	protected $pageTemplatePath = 'scyzoryk/maps.xml';


	protected function prepareView()
	{
		$this->view->maps = $this->browser->getMaps();
	}


	protected function runCommands()
	{
		//add new row
		if(isset($_POST['newId'], $_POST['newName']) && $_POST['newId'])
		{
			$params = array('map_id' => $_POST['newId'], 'name' => $_POST['newName']);
			$this->editor->updateRow(new Daemon_Scyzoryk_DbRowMap($params));
			return true;
		}
		//delete rows
		if(isset($_POST['del']))
		{
			$this->editor->deleteMaps($_POST['del']);
			return true;
		}
		return false;
	}
}


$ctrl = new Daemon_Scyzoryk_Controller_Maps($cfg);
$ctrl->execute();
