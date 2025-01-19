<?php
//@author Krzysztof Sikorski
$cfg = require_once'../_init.php';


class Daemon_Scyzoryk_Controller_LocationEventEdit extends Daemon_Scyzoryk_Controller
{
	protected $pageSubtitle = 'Lokacje';
	protected $pageTemplatePath = 'scyzoryk/location-event-edit.xml';
	private $monster;


	protected function prepareModel()
	{
		$this->event = $this->editor->selectRow('Daemon_Scyzoryk_DbRowLocationEvent', $this->editId, $this->editId2);
	}


	protected function prepareView()
	{
		$this->pageSubtitleDetails = $this->event ? 'edycja zdarzenia specjalnego' : null;
		$this->view->event = $this->event;
	}


	protected function runCommands()
	{
		if(is_null($this->event))
			return false;
		if(isset($_POST['chance'], $_POST['params']))
		{
			$this->event->chance = $_POST['chance'];
			$this->event->params = $_POST['params'];
			$this->editor->updateRow($this->event);
			return true;
		}
		return false;
	}
}


$ctrl = new Daemon_Scyzoryk_Controller_LocationEventEdit($cfg);
$ctrl->execute();
