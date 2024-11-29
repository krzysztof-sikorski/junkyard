<?php
//@author Krzysztof Sikorski
$cfg = require_once'../_init.php';


class Daemon_Scyzoryk_Controller_Search extends Daemon_Scyzoryk_Controller
{
	protected $pageSubtitle = 'Szukaj';
	protected $pageTemplatePath = 'scyzoryk/search.xml';
	private $searchType;
	private $inputId;
	private $inputName;
	private $results = null;


	protected function prepareModel()
	{
		$this->searchType = isset($_GET['type']) ? $_GET['type'] : null;
		$this->inputId = isset($_POST['id']) ? $_POST['id'] : null;
		$this->inputName = isset($_POST['name']) ? $_POST['name'] : null;
	}


	protected function prepareView()
	{
		$this->view->results = $this->results;
		$this->view->inputId = $this->inputId;
		$this->view->inputName = $this->inputName;
	}


	protected function runCommands()
	{
		if(!$this->inputId && !$this->inputName)
			return false;
		$searchTypes = array(
			'l' => array('tableName' => 'locations', 'indexCol' => 'location_id'),
			'm' => array('tableName' => 'monsters', 'indexCol' => 'monster_id'),
			'i' => array('tableName' => 'items', 'indexCol' => 'item_id'),
		);
		if(!isset($searchTypes[$this->searchType]))
			return false;
		$searchType = $searchTypes[$this->searchType];
		$tableName = $searchType['tableName'];
		$indexCol = $searchType['indexCol'];
		$this->results = $this->browser->findRow($tableName, $indexCol, $this->inputId, $this->inputName);
		return true;
	}
}


$ctrl = new Daemon_Scyzoryk_Controller_Search($cfg);
$ctrl->execute();
