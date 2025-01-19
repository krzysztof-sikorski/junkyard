<?php
//@author Krzysztof Sikorski
$cfg = require_once'../_init.php';


class Daemon_Scyzoryk_Controller_EventEdit extends Daemon_Scyzoryk_Controller
{
	protected $pageSubtitle = 'Zdarzenia specjalne';
	protected $pageTemplatePath = 'scyzoryk/event-edit.xml';
	private $editObj;


	protected function prepareModel()
	{
		$this->editObj = new Daemon_DbObject_Event();
		$this->editObj->attachDbClient($this->dbClient);
		if($this->editId)
			$this->editObj->get(array('event_id' => $this->editId));
	}


	protected function prepareView()
	{
		$this->pageSubtitleDetails = $this->editObj ? $this->editObj->name : null;
		$this->view->editObj = $this->editObj;
	}


	protected function runCommands()
	{
		if(isset($_POST['event_id']))
		{
			$this->editObj->import($_POST);
			if($_POST['event_id'])
				$this->editObj->put();
			else Daemon_MsgQueue::add('Uzupełnij ID.');
			return true;
		}
		return false;
	}
}


$ctrl = new Daemon_Scyzoryk_Controller_EventEdit($cfg);
$ctrl->execute();
