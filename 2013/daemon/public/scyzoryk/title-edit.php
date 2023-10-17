<?php
//@author Krzysztof Sikorski
$cfg = require_once'../_init.php';


class Daemon_Scyzoryk_Controller_TitleEdit extends Daemon_Scyzoryk_Controller
{
	protected $pageSubtitle = 'Tytuły';
	protected $pageTemplatePath = 'scyzoryk/title-edit.xml';
	private $title;


	protected function prepareModel()
	{
		$this->title = $this->editor->selectRow('Daemon_Scyzoryk_DbRowTitle', $this->editId);
	}


	protected function prepareView()
	{
		$this->pageSubtitleDetails = $this->title ? $this->editId : null;
		$this->view->title = $this->title;
	}


	protected function runCommands()
	{
		if(is_null($this->title))
			return false;
		if(isset($_POST['id']) && $_POST['id'])
		{
			$this->title->title_id = $_POST['id'];
			$this->title->name_f = Daemon::getArrayValue($_POST, 'name_f');
			$this->title->name_m = Daemon::getArrayValue($_POST, 'name_m');
			$this->title->name_n = Daemon::getArrayValue($_POST, 'name_n');
			$this->title->type = Daemon::getArrayValue($_POST, 'type');
			$this->editor->updateRow($this->title);
			return true;
		}
		return false;
	}
}


$ctrl = new Daemon_Scyzoryk_Controller_TitleEdit($cfg);
$ctrl->execute();
