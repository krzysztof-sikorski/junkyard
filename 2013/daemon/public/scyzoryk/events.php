<?php
//@author Krzysztof Sikorski
$cfg = require_once'../_init.php';


class Daemon_Scyzoryk_Controller_Events extends Daemon_Scyzoryk_Controller
{
	protected $pageSubtitle = 'Zdarzenia specjalne';
	protected $pageTemplatePath = 'scyzoryk/events.xml';


	protected function prepareView()
	{
		$sql = "SELECT * FROM events ORDER BY event_id";
		$this->view->items = $this->dbClient->selectAll($sql);
	}


	protected function runCommands()
	{
		//add new row
		if(isset($_POST['newId'], $_POST['newName']) && $_POST['newId'])
		{
			$object = new Daemon_DbObject_Event();
			$object->attachDbClient($this->dbClient);
			$object->event_id = $_POST['newId'];
			$object->name = $_POST['newName'];
			$object->put();
			return true;
		}
		//delete rows
		if(isset($_POST['del']) && is_array($_POST['del']))
		{
			$this->editor->deleteRows('events', 'event_id', $_POST['del']);
			return true;
		}
		return false;
	}
}


$ctrl = new Daemon_Scyzoryk_Controller_Events($cfg);
$ctrl->execute();
