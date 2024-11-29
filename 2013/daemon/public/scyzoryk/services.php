<?php
//@author Krzysztof Sikorski
$cfg = require_once'../_init.php';


class Daemon_Scyzoryk_Controller_Page extends Daemon_Scyzoryk_Controller
{
	protected $pageSubtitle = 'Usługi';
	protected $pageTemplatePath = 'scyzoryk/services.xml';


	protected function prepareView()
	{
		$this->view->services = $this->browser->getServices();
	}


	protected function runCommands()
	{
		//add new row
		if(isset($_POST['newId'], $_POST['newName']) && $_POST['newId'])
		{
			$params = array('service_id' => $_POST['newId'], 'name' => $_POST['newName']);
			$this->editor->updateRow(new Daemon_Scyzoryk_DbRowService($params));
			return true;
		}
		//delete rows
		if(isset($_POST['del']))
		{
			$this->editor->deleteServices($_POST['del']);
			return true;
		}
		return false;
	}
}


$ctrl = new Daemon_Scyzoryk_Controller_Page($cfg);
$ctrl->execute();
