<?php
//@author Krzysztof Sikorski
class Daemon_Service_Shop extends Daemon_Service
{


	public function execute($params)
	{
		$inventoryObj = new Daemon_Inventory($this->dbClient, $this->characterData);
		//run commands
		$this->isCommand = $this->runCommands($params);
		//generate output
		$equipment = array();
		$inventory = array();
		$items = $inventoryObj->getItems('inventory');
		foreach($items as $key => $row)
		{
			$row['_price'] = ceil($row['item']->value / 2);
			if($row['equipped'])
				$equipment[$key] = $row;
			else $inventory[$key] = $row;
		}
		$storage = $this->bankEnabled ? $inventoryObj->getItems('storage') : array();
		foreach($storage as &$row)
			$row['_price'] = ceil($row['item']->value / 2);
		$this->view->shopName = $this->serviceData['name'];
		$this->view->shopDescription = $this->serviceData['description'];
		$this->view->goldBank = $this->characterData->gold_bank;
		$this->view->goldPurse = $this->characterData->gold_purse;
		$this->view->bankEnabled = $this->bankEnabled;
		$this->view->equipment = $equipment;
		$this->view->inventory = $inventory;
		$this->view->storage = $storage;
		$this->view->hasItems = ($equipment || $inventory || $storage);
		$this->view->shopItems = $this->getItems();
		ob_start();
		$this->view->display('service/shop.xml');
		$this->eventLog = ob_get_clean();
	}


	private function runCommands($params)
	{
		//sell item
		if(isset($params['sell']))
		{
			$this->buyFromCharacter($params['sell']);
			return true;
		}
		//buy item(s)
		if(isset($params['buy'], $params['amount']))
		{
			$this->sellToCharacter($params['buy'], $params['amount'], !empty($params['bind']));
			return true;
		}
		return false;
	}


	//buys item from character
	public function buyFromCharacter(array $ids)
	{
		foreach($ids as $inventoryId)
		{
			$cond = "inv.inventory_id=:id AND character_id=:charId";
			$params = array('id' => $inventoryId, 'charId' => $this->characterData->character_id);
			if(!$this->bankEnabled)
				$cond .= " AND inv.status!='storage'";
			$sql = "SELECT i.item_id, i.name, i.value, inv.equipped
				FROM inventory inv JOIN items i USING(item_id) WHERE $cond";
			if($item = $this->dbClient->selectRow($sql, $params))
			{
				$sql = "DELETE FROM inventory WHERE inventory_id=:id";
				$this->dbClient->query($sql, array('id' => $inventoryId));
				$buyPrice = ceil($item['value'] / 2);
				if($this->bankEnabled)
					$this->characterData->gold_bank += $buyPrice;
				else $this->characterData->gold_purse += $buyPrice;
				$this->characterData->put();
				//add to shop offer
				$sql = "INSERT INTO service_items (service_id, item_id, type, quantity)
					VALUES (:serviceId, :itemId, 'drop', 1) ON DUPLICATE KEY UPDATE quantity = quantity + 1";
				$params = array('serviceId' => $this->serviceData['service_id'], 'itemId' => $item['item_id']);
				$this->dbClient->query($sql, $params);
				Daemon_MsgQueue::add("Sprzedajesz $item[name] za $buyPrice zł.");
				//update character stats if item was equipped
				if($item['equipped'])
				{
					$this->characterData->resetCombatStats();
					$this->characterData->put();
				}
			}
			else Daemon_MsgQueue::add('Wybrany przedmiot nie istnieje.');
		}
	}


	//fetches shop's offer, grouped by item type
	public function getItems()
	{
		$result = array();
		$sql = "SELECT s.item_id, s.type, s.quantity
			FROM service_items s JOIN items i USING(item_id)
			WHERE service_id=:id ORDER BY i.type ASC, s.type ASC, i.damage_type ASC, i.name ASC";
		$params = array('id' => $this->serviceData['service_id']);
		if($data = $this->dbClient->selectAll($sql, $params))
		{
			$characterGold = $this->characterData->gold_purse;
			if($this->bankEnabled)
				$characterGold += $this->characterData->gold_bank;
			foreach($data as $row)
			{
				$item = new Daemon_DbObject_Item();
				$item->attachDbClient($this->dbClient);
				$item->get(array('item_id' => $row['item_id']));
				$type = $item->type;
				$item->_price = $this->sellPrice($item->value, $row['quantity']);
				$item->_drop = ('normal' != $row['type']);
				$item->_quantity = $row['quantity'];
				$item->_canBuy = ($item->_price <= $characterGold);
				$item->_soldOff = ($item->_drop && ($item->_quantity < 1));
				if(!empty($this->_flags['temple']))
					$item->_canBind = ($item->value + $item->_price <= $characterGold);
				else $item->_canBind = false;
				$result[$type]['items'][$row['item_id']] = $item;
			}
		}
		$groupNames = Daemon_Dictionary::$equipmentGroups;
		foreach(array_keys($result) as $key)
		{
			if(isset($groupNames[$key]))
				$result[$key]['name'] = $groupNames[$key];
			else unset($groupNames[$key]);
		}
		return $result;
	}


	//calculates item price
	public function sellPrice($value, $quantity)
	{
		if($quantity > 0)
		{
			$mult = 1 + 4 * exp(-$quantity / 64);
			return ceil($value * $mult);
		}
		else return $value;
	}


	//sells item to character
	public function sellToCharacter($itemId, $amount, $bind)
	{
		$amount = max(1, $amount);
		if(!$this->templeEnabled)
			$bind = false;
		//fetch item data
		$sql = "SELECT s.*, i.name, i.value FROM service_items s JOIN items i USING(item_id)
			WHERE service_id=:serviceId AND item_id=:itemId";
		$params = array('serviceId' => $this->serviceData['service_id'], 'itemId' => $itemId);
		$item = $this->dbClient->selectRow($sql, $params);
		//check availability
		if(!$item)
		{
			Daemon_MsgQueue::add('Wybrany przedmiot nie jest dostępny.');
			return false;
		}
		if(('normal' != $item['type']) && ($amount > $item['quantity']))
		{
			Daemon_MsgQueue::add('Nie ma tyle towaru w ofercie.');
			return false;
		}
		//calculate total cost
		$totalCost = 0;
		for($i = 0; $i < $amount; ++$i)
			$totalCost += $this->sellPrice($item['value'], $item['quantity'] - $i);
		if($bind)
			$totalCost += $amount * $item['value'];
		//check character gold
		if(!$this->characterData->payGold($totalCost, $this->bankEnabled))
			return false;
		//update service
		if('normal' != $item['type'])
		{
			$sql = "UPDATE service_items SET quantity = quantity - :amount
				WHERE service_id=:serviceId AND item_id=:itemId";
			$params = array('serviceId' => $this->serviceData['service_id'], 'itemId' => $itemId, 'amount' => $amount);
			$this->dbClient->query($sql, $params);
		}
		//update character
		$sql = "INSERT INTO inventory(character_id, item_id, flags) VALUES (:charId, :itemId, :flags)";
		$params = array('charId' => $this->characterData->character_id, 'itemId' => $itemId);
		$params['flags'] = $bind ? 'bound,identified' : 'identified';
		for($i = 0; $i < $amount; ++$i)
			$this->dbClient->query($sql, $params);
		//show message
		if($amount > 1)
			Daemon_MsgQueue::add("Kupujesz {$amount}x $item[name] za łączną kwotę $totalCost zł.");
		else Daemon_MsgQueue::add("Kupujesz $item[name] za $totalCost zł.");
		return true;
	}
}
