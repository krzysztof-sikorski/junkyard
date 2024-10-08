<?php
//@author Krzysztof Sikorski
class Daemon_Event
{
	private $characterData = null;
	private $eventLog;


	public function attachCharacterData(Daemon_DbObject_CharacterData $characterData)
	{
		$this->characterData = $characterData;
	}


	public function attachDbClient(Daemon_DbClient $dbClient)
	{
		$this->dbClient = $dbClient;
	}


	public function getEventLog()
	{
		return $this->eventLog;
	}


	public function execute(Daemon_View $view, $eventId, $params)
	{
		//fetch event info
		$sql = "SELECT name, handle FROM events WHERE event_id=:id";
		$event = $this->dbClient->selectRow($sql, array('id' => $eventId));
		if(!$event)
			$event = array('name' => '???', 'handle' => null);
		//check if event is implemented
		$className = "Daemon_Event_$event[handle]";
		if(class_exists($className, true) && is_subclass_of($className, 'Daemon_EventInterface'))
		{
			//valid event, execute it (may update character)
			$handler = new $className($this->dbClient, $this->characterData, $view);
			$this->eventLog = $handler->execute($params);
		}
		else
		{
			//no event, update character
			Daemon_MsgQueue::add("Nieznane zdarzenie: $event[name]");
			$this->characterData->setLocationEvent(array());
			$this->characterData->put();
		}
	}
}
