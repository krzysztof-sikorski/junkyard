<?php
//@author Krzysztof Sikorski
class Daemon_Scyzoryk_Controller
{
	//global objects
	protected $cfg;
	protected $browser;
	protected $dbClient;
	protected $editor;
	protected $view;
	//execution parameters
	protected $editId = null;
	protected $editId2 = null;
	//output parameters
	protected $pageSubtitle = null;
	protected $pageSubtitleDetails = null;
	protected $pageTemplatePath;


	final public function __construct(Daemon_Config $cfg)
	{
		$this->cfg = $cfg;
		session_name($this->cfg->sessionName);
		session_cache_limiter(null);
		session_start();
		$this->dbClient = Daemon::createDbClient($this->cfg);
		$this->browser = new Daemon_Scyzoryk_Browser($this->dbClient);
		$this->editor = new Daemon_Scyzoryk_Editor($this->dbClient);
		$this->view = new Daemon_View($this->cfg);
		$this->editId = isset($_GET['id']) ? $_GET['id'] : null;
		$this->editId2 = isset($_GET['id2']) ? $_GET['id2'] : null;
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
		$this->view->setPageTitle($this->pageSubtitle, $this->pageSubtitleDetails, $cmdExecuted);
		$this->view->setMessages(Daemon_MsgQueue::getAll());
		$this->view->display($this->pageTemplatePath, Daemon_View::MODE_HTML);
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
}
