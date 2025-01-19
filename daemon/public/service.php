<?php
//@author Krzysztof Sikorski
$cfg = require_once'./_init.php';


class Daemon_Controller_Page extends Daemon_Controller
{
	protected $pageSubtitle = 'Usługi';
	protected $pageTemplatePath = 'service.xml';
	protected $requireActiveChar = true;
	protected $requireAuthentication = true;
	protected $requireFactionMatch = true;
	protected $requireLocation = true;
	protected $requireNoEvents = true;
	private $service;
	private $inventory;
	private $handler;
	private $bankEnabled = false;
	private $templeEnabled = false;


	public function prepareModel()
	{
		if(!$this->characterData->character_id)
			return;//logged out
		//check available services
		$services = $this->location->getServices();
		$serviceId = isset($_GET['id']) ? $_GET['id'] : null;
		$this->service = null;
		$this->bankEnabled = false;
		$this->templeEnabled = false;
		foreach ($services as $srv)
		{
			if (empty($srv['_enabled']))
				continue;
			if ($srv['service_id'] == $serviceId)
				$this->service = $srv;
			if ($srv['type'] == 'bank')
				$this->bankEnabled = true;
			elseif ($srv['type'] == 'temple')
				$this->templeEnabled = true;
		}
		if(empty($this->service))
		{
			Daemon_MsgQueue::add('Wybrana usługa nie istnieje lub jest niedostępna.');
			Daemon::redirect($this->cfg->getUrl('map'));
			exit;
		}
		//prepare service handler
		$classes = array(
			'bank' => 'Daemon_Service_Bank',
			'healer' => 'Daemon_Service_Healer',
			'shop' => 'Daemon_Service_Shop',
			'temple' => 'Daemon_Service_Temple',
		);
		if (isset($classes[$this->service['type']]))
			$className = $classes[$this->service['type']];
		else
			$className = null;
		if(class_exists($className, true) && is_subclass_of($className, 'Daemon_Service'))
		{
			$this->handler = new $className($this->dbClient, $this->characterData, $this->view,
				$this->service, $this->bankEnabled, $this->templeEnabled);
		}
		else
		{
			Daemon_MsgQueue::add('Nieznany typ usługi.');
			Daemon::redirect($this->cfg->getUrl('map'));
			exit;
		}
	}


	public function prepareView()
	{
		$this->pageSubtitleDetails = $this->service['name'];
		$this->view->eventLog = $this->handler->getEventLog();
	}


	public function runCommands()
	{
		$this->handler->execute($_POST);
		return $this->handler->isCommand();
	}
}


$ctrl = new Daemon_Controller_Page($cfg);
$ctrl->execute();
