<?php
//@author Krzysztof Sikorski
$cfg = require_once'./_init.php';


class Daemon_Controller_Map extends Daemon_Controller
{
	protected $pageSubtitle = 'Otoczenie';
	protected $pageTemplatePath = 'map.xml';
	protected $requireActiveChar = true;
	protected $requireAuthentication = true;
	protected $requireLocation = true;
	private $eventLog = null;


	public function prepareView()
	{
		if($this->eventLog)
		{
			$this->pageSubtitle = 'Zdarzenie';
			$this->pageTemplatePath = 'event.xml';
			$this->view->eventLog = $this->eventLog;
			return;
		}
		$bossStatuses = Daemon_Dictionary::$bossStatuses;
		if(isset($bossStatuses[$this->location->boss_status]))
			$this->location->boss_status_name = $bossStatuses[$this->location->boss_status];
		else
			$this->location->boss_status_name = null;
		$this->pageSubtitleDetails = $this->location->name;
		$this->view->locationDesc = nl2br(htmlspecialchars($this->location->description));
		$this->view->pictureUrl = $this->location->getPictureUrl();
		$this->view->region = $this->location->getRegionName();
		$this->view->faction = $this->location->getFactionName();
		$this->view->maps = $this->location->getMaps();
		$this->view->paths = $this->location->getPaths();
		$this->view->services = $this->location->getServices();
		$this->view->lastMission = $this->characterData->getLastMission('completed');
		//prepare character list
		$showAll = isset($_GET['more']);
		$halfLimit = $showAll ? null : (int) ceil($this->dbCfg->listLimitCharacters/2);
		$n = $this->location->getCharacterCount(2*$halfLimit+1);
		$characters = $this->location->getCharacters($this->characterData, $halfLimit);
		$this->view->characters = $characters;
		$this->view->showMoreLink = !$showAll && ($n > 2*$halfLimit);
	}


	public function runCommands()
	{
		$isCommand = false;
		//actions
		if(isset($_POST['act']))
		{
			switch($_POST['act'])
			{
				case'train':
					$this->location->actionTrain();
					break;
				case'rest':
					$this->location->actionRest();
					break;
				case'hunt':
					$this->location->actionHunt();
					break;
			}
			$isCommand = true;
		}
		//travel
		if(isset($_POST['travel']))
		{
			$this->location->actionTravel($_POST['travel']);
			$isCommand = true;
		}
		//run events
		$this->view->setGameHeader($this->player->getPlayerId(),
			$this->activeCharacter, $this->characterData, $this->location);
		$this->eventLog = $this->characterData->runEvent($this->view);
		//set "isCommand" flag
		return $isCommand;
	}
}


$ctrl = new Daemon_Controller_Map($cfg);
$ctrl->execute();
