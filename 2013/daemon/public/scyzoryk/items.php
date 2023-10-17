<?php
//@author Krzysztof Sikorski
$cfg = require_once'../_init.php';


class Daemon_Scyzoryk_Controller_Items extends Daemon_Scyzoryk_Controller
{
	protected $pageSubtitle = 'Przedmioty';
	protected $pageTemplatePath = 'scyzoryk/items.xml';
	private $filter;


	protected function prepareModel()
	{
		$this->filter = new Daemon_Scyzoryk_Filter('items', array('type'));
	}


	protected function prepareView()
	{
		$this->view->filter = $this->filter;
		$this->view->items = $this->browser->getItems($this->filter);
		$this->view->itemTypes = Daemon_Dictionary::$itemTypes;
		$this->view->itemWeaponTypes = Daemon_Dictionary::$itemWeaponTypes;
		$this->view->itemDamageTypes = Daemon_Dictionary::$itemDamageTypes;
		$this->view->itemArmorTypes = Daemon_Dictionary::$itemArmorTypes;
	}


	protected function runCommands()
	{
		//add new row
		if(isset($_POST['newId'], $_POST['newName'], $_POST['newType']) && $_POST['newId'])
		{
			$item = new Daemon_DbObject_Item();
			$item->attachDbClient($this->dbClient);
			$item->item_id = $_POST['newId'];
			$item->name = $_POST['newName'];
			$item->type = $_POST['newType'];
			$item->put();
			return true;
		}
		//delete rows
		if(isset($_POST['del']))
		{
			$this->editor->deleteItems($_POST['del']);
			return true;
		}
		//set filter
		if(isset($_POST['filter']) && is_array($_POST['filter']))
		{
			foreach($_POST['filter'] as $name => $value)
				$this->filter->$name = $value;
			return true;
		}
		return false;
	}
}


$ctrl = new Daemon_Scyzoryk_Controller_Items($cfg);
$ctrl->execute();
