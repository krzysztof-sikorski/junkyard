<?php
//@author Krzysztof Sikorski
$cfg = require_once'./_init.php';


class Daemon_Controller_Temple extends Daemon_Controller
{
	protected $pageSubtitle = 'Świątynia';
	protected $pageTemplatePath = 'temple.xml';
	protected $requireActiveChar = true;
	protected $requireAuthentication = true;
	protected $requireFactionMatch = true;
	protected $requireLocation = true;
	protected $requireNoEvents = true;
	private $bankEnabled;
	private $temple;
	private $inventory;


	public function prepareModel()
	{
		if(!$this->characterData->character_id)
			return;//logged out
		$flags = $this->location->getFlags();
		$this->bankEnabled = !empty($flags['bank']);
		if(empty($flags['temple']))
		{
			Daemon_MsgQueue::add('W tej lokacji nie ma świątyni.');
			Daemon::redirect($this->cfg->getUrl('map'));
			exit;
		}
		$this->temple = new Daemon_Temple($this->dbClient, $this->characterData,
			$this->bankEnabled, $this->location->faction_id, $this->dbCfg);
		$this->temple->locationId = $this->location->location_id;
		$this->inventory = new Daemon_Inventory($this->dbClient, $this->characterData);
	}


	public function prepareView()
	{
		$this->view->temple = $this->temple;
		$this->view->bankEnabled = $this->bankEnabled;
		$this->view->itemsToBind = $this->temple->getItems(false, true);
		$this->view->itemsToOffer = $this->temple->getItems(true, false);
		$this->view->lastMission = $this->characterData->getLastMission('completed');
	}


	public function runCommands()
	{
		//bind item
		if(isset($_POST['bind']))
		{
			$this->temple->bindItem($_POST['bind']);
			return true;
		}
		//pray at altar
		if(isset($_POST['pray']))
		{
			$inventoryId = isset($_POST['offer']) ? $_POST['offer'] : null;
			$this->temple->pray($_POST['pray'], $inventoryId);
			return true;
		}
		//give up mission
		if(isset($_POST['giveUp']))
		{
			$this->temple->removeMission();
			return true;
		}
		//check mission
		$this->temple->checkMission();
		return false;
	}
}


$ctrl = new Daemon_Controller_Temple($cfg);
$ctrl->execute();
