<?php
//@author Krzysztof Sikorski
$cfg = require_once'../_init.php';


class Daemon_Scyzoryk_Controller_Locations extends Daemon_Scyzoryk_Controller
{
	protected $pageSubtitle = 'Lokacje';
	protected $pageTemplatePath = 'scyzoryk/locations.xml';
	private $filter;


	protected function prepareModel()
	{
		$this->filter = new Daemon_Scyzoryk_Filter('locations', array('region_id'));
	}


	protected function prepareView()
	{
		$this->view->filter = $this->filter;
		$this->view->locations = $this->browser->getLocations($this->filter);
		$this->view->regions = $this->browser->getRegions();
		$this->view->locationTypes = Daemon_Dictionary::$locationTypes;
	}


	protected function runCommands()
	{
		//add new row
		if(isset($_POST['newId'], $_POST['newName'], $_POST['newRegion']) && $_POST['newId'])
		{
			$object = new Daemon_DbObject_Location();
			$object->attachDbClient($this->dbClient);
			$object->location_id = $_POST['newId'];
			$object->name = $_POST['newName'];
			$object->region_id = $_POST['newRegion'];
			$object->put();
			return true;
		}
		//delete rows
		if(isset($_POST['del']))
		{
			$this->editor->deleteLocations($_POST['del']);
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


$ctrl = new Daemon_Scyzoryk_Controller_Locations($cfg);
$ctrl->execute();
