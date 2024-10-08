<?php
//@author Krzysztof Sikorski
abstract class Daemon_EventInterface
{
	protected $characterData;
	protected $dbClient;
	protected $view;


	final public function __construct(Daemon_DbClient $dbClient,
		Daemon_DbObject_CharacterData $characterData, Daemon_View $view)
	{
		$this->dbClient = $dbClient;
		$this->characterData = $characterData;
		$this->view = $view;
	}


	final protected function clearEvent()
	{
		$this->characterData->setLocationEvent(array());
		$this->characterData->put();
	}


	abstract public function execute($params);
}
