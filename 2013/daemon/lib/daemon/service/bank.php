<?php
//@author Krzysztof Sikorski
class Daemon_Service_Bank extends Daemon_Service
{
	const STORAGE_LIMIT = 5;//base limit


	public function execute($params)
	{
		$inventory = new Daemon_Inventory($this->dbClient, $this->characterData);
		//run commands
		$this->isCommand = $this->runCommands($params);
		//generate output
		$items = $inventory->getItems('inventory', true);
		$storage = $inventory->getItems('storage');
		$storageLimit = $this->getStorageLimit();
		$this->view->goldBank = $this->characterData->gold_bank;
		$this->view->goldPurse = $this->characterData->gold_purse;
		$this->view->inventory = $items;
		$this->view->storage = $storage;
		$this->view->storageLimit = $storageLimit;
		$this->view->storageFull = (count($storage) >= $storageLimit);
		ob_start();
		$this->view->display('service/bank.xml');
		$this->eventLog = ob_get_clean();
	}


	private function runCommands($params)
	{
		//gold operations
		if(isset($params['getGold'], $params['putGold']))
		{
			$this->goldOperations($params['getGold'], $params['putGold']);
			return true;
		}
		//item operations
		if(isset($params['getItem']))
		{
			$this->getItem($params['getItem']);
			return true;
		}
		if(isset($params['putItem']))
		{
			$this->putItem($params['putItem']);
			return true;
		}
		return false;
	}


	//fetches item from storage
	private function getItem($inventoryId)
	{
		$sql = "UPDATE inventory SET status='inventory' WHERE inventory_id=:id AND character_id=:charId";
		$params = array('id' => $inventoryId, 'charId' => $this->characterData->character_id);
		$this->dbClient->query($sql, $params);
	}


	private function getStorageLimit()
	{
		return self::STORAGE_LIMIT + max(0, floor(log($this->characterData->level)));
	}


	//puts item into storage
	private function putItem($inventoryId)
	{
		$sql = "SELECT COUNT(1) FROM inventory WHERE character_id=:charId AND status='storage'";
		$n = $this->dbClient->selectValue($sql, array('charId' => $this->characterData->character_id));
		if($n < $this->getStorageLimit())
		{
			$sql = "UPDATE inventory SET status='storage' WHERE inventory_id=:id AND character_id=:charId AND equipped IS NULL";
			$params = array('id' => $inventoryId, 'charId' => $this->characterData->character_id);
			$this->dbClient->query($sql, $params);
		}
		else Daemon_MsgQueue::add('Twój schowek jest pełny.');
	}


	private function goldOperations($getGold, $putGold)
	{
		//get gold
		$delta = max(0, min((int) $getGold, $this->characterData->gold_bank));
		$this->characterData->gold_purse += $delta;
		$this->characterData->gold_bank -= $delta;
		//put gold
		$delta = max(0, min((int) $putGold, $this->characterData->gold_purse));
		$this->characterData->gold_bank += $delta;
		$this->characterData->gold_purse -= $delta;
		//store mods
		$this->characterData->put();
	}
}
