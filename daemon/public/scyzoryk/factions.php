<?php
//@author Krzysztof Sikorski
$cfg = require_once'../_init.php';


class Daemon_Scyzoryk_Controller_Factions extends Daemon_Scyzoryk_Controller
{
	protected $pageSubtitle = 'Frakcje';
	protected $pageTemplatePath = 'scyzoryk/factions.xml';
	private $factions;


	protected function prepareView()
	{
		$this->view->factions = $this->browser->getFactions();
	}


	protected function runCommands()
	{
		//add new row
		if(isset($_POST['newId'], $_POST['newName']) && $_POST['newId'])
		{
			$params = array('faction_id' => $_POST['newId'], 'name' => $_POST['newName']);
			$this->editor->updateRow(new Daemon_Scyzoryk_DbRowFaction($params));
			return true;
		}
		//delete rows
		if(isset($_POST['del']))
		{
			$this->editor->deleteFactions($_POST['del']);
			return true;
		}
		return false;
	}
}


$ctrl = new Daemon_Scyzoryk_Controller_Factions($cfg);
$ctrl->execute();
