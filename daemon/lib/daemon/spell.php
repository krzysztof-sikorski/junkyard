<?php
//@author Krzysztof Sikorski
class Daemon_Spell
{
	private $characterData = null;
	private $usageLog;


	public function attachCharacterData(Daemon_DbObject_CharacterData $characterData)
	{
		$this->characterData = $characterData;
	}


	public function attachDbClient(Daemon_DbClient $dbClient)
	{
		$this->dbClient = $dbClient;
	}


	public function getUsageLog()
	{
		return $this->usageLog;
	}


	public function execute(Daemon_View $view, $spellId)
	{
		//fetch spell info
		$sql = "SELECT name, handle FROM spells WHERE spell_id=:id";
		$spell = $this->dbClient->selectRow($sql, array('id' => $spellId));
		if(!$spell)
			$spell = array('name' => '???', 'handle' => null);
		//get spell's cost
		$cost = null;
		foreach($this->characterData->getSpells() as $row)
		{
			if($row['spell_id'] == $spellId)
				$cost = $row['_cost'];
		}
		if(!$cost)
		{
			Daemon_MsgQueue::add('Nie znasz tego zaklęcia.');
			return false;
		}
		//check if handler is implemented
		$className = "Daemon_Spell_$spell[handle]";
		if(class_exists($className, true) && is_subclass_of($className, 'Daemon_SpellInterface'))
		{
			//valid spell, execute it
			$handler = new $className($this->dbClient, $this->characterData, $view);
			$this->usageLog = $handler->execute($spellId, $cost);
			$this->characterData->put();
			return true;
		}
		else
		{
			//no effect
			Daemon_MsgQueue::add("Nieznany efekt: $spell[name]");
			return false;
		}
	}
}
