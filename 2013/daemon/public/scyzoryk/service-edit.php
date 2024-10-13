<?php
//@author Krzysztof Sikorski
$cfg = require_once'../_init.php';


class Daemon_Scyzoryk_Controller_Page extends Daemon_Scyzoryk_Controller
{
	protected $pageSubtitle = 'Usługi';
	protected $pageTemplatePath = 'scyzoryk/service-edit.xml';
	private $service;


	protected function prepareModel()
	{
		$this->service = $this->editor->selectRow('Daemon_Scyzoryk_DbRowService', $this->editId);
	}


	protected function prepareView()
	{
		$this->pageSubtitleDetails = $this->service ? $this->service->name : null;
		$this->view->service = $this->service;
		$this->view->serviceItems = $this->browser->getServiceItems($this->editId);
		$this->view->factions = $this->browser->getFactions();
		$this->view->serviceTypes = Daemon_Dictionary::$serviceTypes;
	}


	protected function runCommands()
	{
		if(is_null($this->service))
			return false;
		if(isset($_POST['id'], $_POST['name'], $_POST['type']) && $_POST['id'])
		{
			$this->service->service_id = $_POST['id'];
			$this->service->name = $_POST['name'];
			$this->service->type = $_POST['type'];
			$this->service->faction_id = empty($_POST['faction_id']) ? null : $_POST['faction_id'];
			$this->service->rank_id = empty($_POST['rank_id']) ? null : (int) $_POST['rank_id'];
			$this->service->description = Daemon::getArrayValue($_POST, 'desc');
			$this->editor->updateRow($this->service);
			return true;
		}
		//add item
		if(isset($_POST['addItem'], $_POST['id']) && $_POST['id'])
		{
			$params = array('service_id' => $this->editId, 'item_id' => $_POST['id']);
			$row = new Daemon_Scyzoryk_DbRowServiceItem($params);
			$this->editor->updateRow($row);
			return true;
		}
		//delete items
		if(isset($_POST['del']))
		{
			$this->editor->deleteServiceItems($this->editId, $_POST['del']);
			return true;
		}
		return false;
	}
}


$ctrl = new Daemon_Scyzoryk_Controller_Page($cfg);
$ctrl->execute();
