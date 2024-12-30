<?php
//@author Krzysztof Sikorski
$cfg = require_once'../_init.php';


class Daemon_Scyzoryk_Controller_LocationEdit extends Daemon_Scyzoryk_Controller
{
	protected $pageSubtitle = 'Lokacje';
	protected $pageTemplatePath = 'scyzoryk/location-edit.xml';
	private $editObj;


	protected function prepareModel()
	{
		$this->editObj = new Daemon_DbObject_Location();
		$this->editObj->attachDbClient($this->dbClient);
		if($this->editId)
			$this->editObj->get(array('location_id' => $this->editId));
	}


	protected function prepareView()
	{
		$this->pageSubtitleDetails = $this->editObj ? $this->editObj->name : null;
		$this->view->editObj = $this->editObj;
		$this->view->paths = $this->browser->getLocationPaths($this->editId);
		$this->view->monsters = $this->browser->getLocationMonsters($this->editId);
		$this->view->events = $this->browser->getLocationEvents($this->editId);
		$this->view->services = $this->browser->getLocationServices($this->editId);
		$this->view->regions = $this->browser->getRegions();
		$this->view->factions = $this->browser->getFactions();
		$this->view->locationTypes = Daemon_Dictionary::$locationTypes;
		$this->view->bossStatuses = Daemon_Dictionary::$bossStatuses;
		//event names
		$sql = "SELECT event_id, name FROM events ORDER BY event_id";
		$this->view->eventNames = $this->dbClient->selectAll($sql);
		//service names
		$sql = "SELECT service_id, name, type FROM services ORDER BY service_id";
		$this->view->serviceNames = $this->dbClient->selectAll($sql);
	}


	protected function runCommands()
	{
		if(isset($_POST['location_id']))
		{
			$this->editObj->import($_POST);
			if($_POST['location_id'])
				$this->editObj->put();
			else Daemon_MsgQueue::add('Uzupełnij ID.');
			return true;
		}
		//add path
		if(isset($_POST['addPath'], $_POST['id']) && $_POST['id'])
		{
			$params = array('location_id' => $this->editId, 'destination_id' => $_POST['id']);
			$row = new Daemon_Scyzoryk_DbRowLocationPath($params);
			$this->editor->updateRow($row);
			if(!empty($_POST['bidir']))
			{
				$params = array('location_id' => $_POST['id'], 'destination_id' => $this->editId);
				$row = new Daemon_Scyzoryk_DbRowLocationPath($params);
				$this->editor->updateRow($row);
			}
			return true;
		}
		//delete paths
		if(isset($_POST['delPaths']))
		{
			$delPathsRev = (array) Daemon::getArrayValue($_POST, 'delPathsRev', array());
			$this->editor->deleteLocationPaths($this->editId, $_POST['delPaths'], $delPathsRev);
			return true;
		}
		//add monster
		if(isset($_POST['addMonster'], $_POST['id']) && $_POST['id'])
		{
			$params = array('location_id' => $this->editId, 'monster_id' => $_POST['id']);
			$row = new Daemon_Scyzoryk_DbRowLocationMonster($params);
			$this->editor->updateRow($row);
			return true;
		}
		//delete monsters
		if(isset($_POST['delMonster']))
		{
			$this->editor->deleteLocationMonsters($this->editId, $_POST['delMonster']);
			return true;
		}
		//add event
		if(isset($_POST['addEvent'], $_POST['id']) && $_POST['id'])
		{
			$params = array('location_id' => $this->editId, 'event_id' => $_POST['id']);
			$row = new Daemon_Scyzoryk_DbRowLocationEvent($params);
			$this->editor->updateRow($row);
			return true;
		}
		//delete events
		if(isset($_POST['delEvent']))
		{
			$this->editor->deleteLocationEvents($this->editId, $_POST['delEvent']);
			return true;
		}
		//add service
		if(isset($_POST['addService'], $_POST['id']) && $_POST['id'])
		{
			$params = array('location_id' => $this->editId, 'service_id' => $_POST['id']);
			$row = new Daemon_Scyzoryk_DbRowLocationService($params);
			$this->editor->updateRow($row);
			return true;
		}
		//delete services
		if(isset($_POST['delService']))
		{
			$this->editor->deleteLocationServices($this->editId, $_POST['delService']);
			return true;
		}
		return false;
	}
}


$ctrl = new Daemon_Scyzoryk_Controller_LocationEdit($cfg);
$ctrl->execute();
