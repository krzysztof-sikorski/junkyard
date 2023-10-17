<?php
//@author Krzysztof Sikorski
$cfg = require_once'../_init.php';


class Daemon_Scyzoryk_Controller_Page extends Daemon_Scyzoryk_Controller
{
	protected $pageSubtitle = 'Szablony przedmiotów';
	protected $pageTemplatePath = 'scyzoryk/item-templates.xml';


	protected function prepareView()
	{
		$this->view->rows = $this->browser->getItemTemplates();
	}


	protected function runCommands()
	{
		//add new row
		if(isset($_POST['newId'], $_POST['newName']) && $_POST['newId'])
		{
			$item = new Daemon_DbObject_ItemTemplate();
			$item->attachDbClient($this->dbClient);
			$item->id = $_POST['newId'];
			$item->name = $_POST['newName'];
			$item->put();
			return true;
		}
		//delete rows
		if(isset($_POST['del']))
		{
			$this->editor->deleteItemTemplates($_POST['del']);
			return true;
		}
		return false;
	}
}


$ctrl = new Daemon_Scyzoryk_Controller_Page($cfg);
$ctrl->execute();
