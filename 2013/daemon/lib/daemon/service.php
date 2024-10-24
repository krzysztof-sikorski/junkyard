<?php
//@author Krzysztof Sikorski
abstract class Daemon_Service
{
	protected $characterData;
	protected $dbClient;
	protected $view;
	protected $isCommand = false;
	protected $eventLog = null;
	protected $bankEnabled = false;
	protected $templeEnabled = false;


	final public function __construct(Daemon_DbClient $dbClient,
		Daemon_DbObject_CharacterData $characterData, Daemon_View $view,
		array $serviceData, $bankEnabled, $templeEnabled)
	{
		$this->dbClient = $dbClient;
		$this->characterData = $characterData;
		$this->view = $view;
		$this->serviceData = $serviceData;
		$this->bankEnabled = $bankEnabled;
		$this->templeEnabled = $templeEnabled;
	}


	final protected function clearEvent()
	{
		$this->characterData->setLocationEvent(array());
		$this->characterData->put();
	}


	final public function getEventLog()
	{
		return $this->eventLog;
	}


	final public function isCommand()
	{
		return $this->isCommand;
	}


	abstract public function execute($params);
}
