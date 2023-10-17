<?php
//@author Krzysztof Sikorski
//prototype for page controller
class Daemon_Controller
{
	//global objects
	protected $cfg = null;
	protected $dbClient = null;
	protected $dbCfg = null;
	protected $activeCharacter = null;
	protected $characterData = null;
	protected $location = null;
	protected $player = null;
	protected $view = null;
	//execution parameters
	protected $disableMessages = false;
	protected $disablePlayer = false;
	protected $requireActiveChar = false;
	protected $requireAuthentication = false;
	protected $requireFactionMatch = false;
	protected $requireLocation = false;
	protected $requireNoEvents = false;
	protected $requiredRole = null;
	//output parameters
	protected $pageOutputMode = Daemon_View::MODE_HTML;
	protected $pageSubtitle = null;
	protected $pageSubtitleDetails = null;
	protected $pageSubtitleUseQuery = false;
	protected $pageTemplatePath;


	final public function __construct(Daemon_Config $cfg)
	{
		$this->cfg = $cfg;
		session_name($this->cfg->sessionName);
		session_cache_limiter(null);
		session_start();
		$this->dbClient = Daemon::createDbClient($this->cfg);
		$this->dbCfg = new Daemon_DbConfig($this->dbClient);
		if(!$this->disablePlayer)
		{
			$this->player = new Daemon_DbObject_Player($this->dbClient);
			$this->activeCharacter = $this->player->getActiveCharacter();
			$this->activeCharacter->updateLastAction();
			$this->characterData = $this->activeCharacter->getCharacterData();
			$this->location = $this->characterData->getLocation();
		}
		$this->view = new Daemon_View($this->cfg);
	}


	//checks last action's timestamp
	final private function checkActionTimestamp()
	{
		$lastAction = isset($_SESSION['ts']) ? $_SESSION['ts'] : 0.0;
		$_SESSION['ts'] = microtime(true);
		return (bool) ($_SESSION['ts'] >= $lastAction + $this->cfg->tsDelta);
	}


	final public function execute()
	{
		//prepare controller
		$this->prepareModel();
		$this->validatePlayer();
		//check last action's timestamp
		if($_POST && !$this->checkActionTimestamp())
		{
			Daemon_MsgQueue::add('Operacja anulowana: za duża częstość.');
			$_POST = array();
		}
		//execute commands
		$cmdExecuted = (bool) $this->runCommands();
		//display page
		$this->prepareView();
		if($this->pageSubtitleUseQuery)
		{
			if($qs = getenv('QUERY_STRING'))
				$this->pageSubtitleDetails = urldecode($qs);
		}
		$this->view->setPageTitle($this->pageSubtitle, $this->pageSubtitleDetails, $cmdExecuted);
		if(!$this->disablePlayer)
		{
			$this->view->setGameHeader($this->player->getPlayerId(),
				$this->activeCharacter, $this->characterData, $this->location);
			$this->view->setPageSkin($this->player->skin);
		}
		else $this->view->setPageSkin(null);
		if(!$this->disableMessages)
			$messages = Daemon_MsgQueue::getAll();
		else $messages = array();
		if($this->dbCfg->globalMessage)
			$messages[] = $this->dbCfg->globalMessage;
		$this->view->setMessages($messages);
		$this->view->display($this->pageTemplatePath, $this->pageOutputMode);
	}


	//page-specific
	protected function prepareModel()
	{
	}


	//page-specific
	protected function prepareView()
	{
	}


	//page-specific
	protected function runCommands()
	{
		return false;
	}


	final private function validatePlayer()
	{
		if($this->disablePlayer)
			return;
		if($this->requireAuthentication && !$this->player->getPlayerId())
		{
			Daemon_MsgQueue::add('Strona dostępna tylko dla zalogowanych użytkowników.');
			Daemon::redirect($this->cfg->getUrl(null));
			exit;
		}
		if($this->requireActiveChar && !$this->player->getCharacterId())
		{
			Daemon_MsgQueue::add('Musisz najpierw wybrać aktywną postać.');
			Daemon::redirect($this->cfg->getUrl('account'));
			exit;
		}
		if($this->requiredRole && !$this->player->hasRole($this->requiredRole))
		{
			Daemon_MsgQueue::add('Nie masz uprawnień do korzystania z tej funkcji.');
			Daemon::redirect($this->cfg->getUrl('account'));
			exit;
		}
		if($this->requireLocation && !$this->location->location_id)
		{
			Daemon::redirect($this->cfg->getUrl('respawn'));
			exit;
		}
		if($this->requireNoEvents && $this->characterData->getLocationEvent())
		{
			Daemon::redirect($this->cfg->getUrl('map'));
			exit;
		}
		if($this->requireFactionMatch && $this->location->faction_id && $this->characterData->faction_id
			&& ($this->location->faction_id != $this->characterData->faction_id))
		{
			Daemon_MsgQueue::add('Odejdź! Nie przyjmujemy takich jak ty!');
			Daemon::redirect($this->cfg->getUrl('map'));
			exit;
		}
	}
}
