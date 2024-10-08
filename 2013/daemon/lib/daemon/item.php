<?php
//@author Krzysztof Sikorski
class Daemon_Item
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


	public function execute(Daemon_View $view, $inventoryId)
	{
		//fetch item data
		$sql = "SELECT s.handle, i.special_param AS params
			FROM inventory inv JOIN items i USING(item_id)
			LEFT JOIN item_specials s ON s.special_id = i.special_type
			WHERE inv.character_id=:charId AND inv.inventory_id=:id AND i.type='item'";
		$params = array('charId' => $this->characterData->character_id, 'id' => $inventoryId);
		$item = $this->dbClient->selectRow($sql, $params);
		if(!$item)
			$item = array('handle' => null, 'params' => null);
		//check if handler is implemented
		$className = "Daemon_Item_$item[handle]";
		if(class_exists($className, true) && is_subclass_of($className, 'Daemon_ItemInterface'))
		{
			//valid event, execute it (may update character)
			$handler = new $className($this->dbClient, $this->characterData, $view);
			$this->usageLog = $handler->execute($inventoryId, $item['params']);
			return true;
		}
		else
		{
			//no effect
			Daemon_MsgQueue::add('Nie masz pojęcia do czego to może służyć.');
			return false;
		}
	}
}
