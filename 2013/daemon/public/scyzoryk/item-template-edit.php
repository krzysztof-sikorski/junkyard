<?php
//@author Krzysztof Sikorski
$cfg = require_once'../_init.php';


class Daemon_Scyzoryk_Controller_Page extends Daemon_Scyzoryk_Controller
{
	protected $pageSubtitle = 'Szablony przedmiotów';
	protected $pageTemplatePath = 'scyzoryk/item-template-edit.xml';
	private $editObj;


	protected function prepareModel()
	{
		$this->editObj = new Daemon_DbObject_ItemTemplate();
		$this->editObj->attachDbClient($this->dbClient);
		if($this->editId)
			$this->editObj->get(array('id' => $this->editId));
	}


	protected function prepareView()
	{
		$this->pageSubtitleDetails = $this->editObj ? $this->editObj->name : null;
		$this->view->editObj = $this->editObj;
	}


	protected function runCommands()
	{
		if(isset($_POST['id']))
		{
			$this->editObj->import($_POST);
			if($_POST['id'])
				$this->editObj->put();
			else Daemon_MsgQueue::add('Uzupełnij ID.');
			return true;
		}
		return false;
	}
}


$ctrl = new Daemon_Scyzoryk_Controller_Page($cfg);
$ctrl->execute();
