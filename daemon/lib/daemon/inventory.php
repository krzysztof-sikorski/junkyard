<?php
//@author Krzysztof Sikorski
class Daemon_Inventory
{
	private $dbClient = null;
	private $characterData = null;


	public function __construct(Daemon_DbClient $dbClient, Daemon_DbObject_CharacterData $characterData)
	{
		$this->dbClient = $dbClient;
		$this->characterData = $characterData;
	}


	//equips selected item
	public function equip($inventoryId, $slot)
	{
		//check item
		$sql = "SELECT item_id FROM inventory WHERE inventory_id=:id AND character_id=:charId";
		$params = array('id' => $inventoryId, 'charId' => $this->characterData->character_id);
		$id = $this->dbClient->selectValue($sql, $params);
		$item = new Daemon_DbObject_Item();
		$item->attachDbClient($this->dbClient);
		$item->get(array('item_id' => $id));
		$slots = $item->getSlots();
		//check slot
		if(!in_array($slot, $slots))
		{
			Daemon_MsgQueue::add('Nie możesz załozyć tego przedmiotu.');
			return false;
		}
		//check for 2h weapon equipped
		$sql = "SELECT 'weapon2h'=i.type FROM inventory inv JOIN items i USING(item_id)
			WHERE character_id=:charId AND equipped='hand_a'";
		$params = array('charId' => $this->characterData->character_id);
		$zweihander = (bool) $this->dbClient->selectValue($sql, $params);
		//equip item, unequip previous item(s)
		if( ('weapon2h' == $item->type) || (('weapon1h' == $item->type) && $zweihander) )//2h equipping or equipped
			$unequipSlots = "'hand_a','hand_b'";
		else $unequipSlots = "'$slot'";
		$sql = "UPDATE inventory SET equipped = IF(inventory_id=:id, :slot, null)
			WHERE character_id=:charId AND (inventory_id=:id OR equipped IN ($unequipSlots))";
		$params = array('id' => $inventoryId, 'charId' => $this->characterData->character_id, 'slot' => $slot);
		$this->dbClient->query($sql, $params);
	}


	//creates equipment array based on items list
	public function getEquipment(array $items)
	{
		$result = array();
		foreach(Daemon_Dictionary::$equipmentSlots as $slot => $name)
			$result[$slot] = array('slotName' => $name, 'inventoryId' => null, 'flags' => null, 'item' => null);
		foreach($items as $row)
		{
			if(isset($result[$row['equipped']]))
			{
				$result[$row['equipped']]['inventoryId'] = $row['inventory_id'];
				$result[$row['equipped']]['flags'] = $row['flags'];
				$result[$row['equipped']]['item'] = $row['item'];
			}
		}
		return $result;
	}


	//fetches a detailed list of character's items
	public function getItems($status, $withoutEquipment = false)
	{
		$cond = "inv.character_id=:characterId";
		if($status)
			$cond .= " AND inv.status=:status";
		if($withoutEquipment)
			$cond .= " AND equipped IS NULL";
		$sql = "SELECT inv.inventory_id, inv.item_id, inv.status, inv.flags, inv.equipped
			FROM inventory inv JOIN items i USING(item_id)
			WHERE $cond ORDER BY i.type, i.name, inv.inventory_id";
		$params = array('characterId' => $this->characterData->character_id);
		if($status)
			$params['status'] = $status;
		if($data = $this->dbClient->selectAll($sql, $params))
		{
			$result = array();
			foreach($data as $row)
			{
				$row['_equipped'] = false;
				if($row['flags'])
					$row['flags'] = array_fill_keys(explode(',', $row['flags']), true);
				else $row['flags'] = array();
				$row['item'] = new Daemon_DbObject_Item();
				$row['item']->attachDbClient($this->dbClient);
				$row['item']->get(array('item_id' => $row['item_id']));
				$result[$row['inventory_id']] = $row;
			}
			return $result;
		}
		else return array();
	}


	//groups items by status (equipped/inventory/storage)
	public function groupItemsByStatus(array $items)
	{
		$result = array();
		foreach($items as $id => $row)
		{
			$status = $row['status'];
			if(('inventory' == $status) && $row['equipped'])
				$status = 'equipment';
			$result[$status]['items'][$id] = $row;
		}
		$groupNames = array('equipment' => 'Ekwipunek', 'inventory' => 'Plecak', 'storage' => 'Schowek');
		foreach(array_keys($result) as $key)
		{
			if(isset($groupNames[$key]))
				$result[$key]['name'] = $groupNames[$key];
			else unset($groupNames[$key]);
		}
		return $result;
	}


	//groups items by type
	public function groupItemsByType(array $items)
	{
		$result = array();
		$groupNames = Daemon_Dictionary::$equipmentGroups;
		foreach ($groupNames as $key => $name)
			$result[$key] = array('name' => $name, 'items' => array());
		foreach($items as $id => $row)
		{
			$type = $row['item']->type;
			$result[$type]['items'][$id] = $row;
		}
		foreach(array_keys($result) as $key)
		{
			if (empty($result[$key]['items']))
				unset($result[$key]);
			elseif (empty($result[$key]['name']))
				$result[$key]['name'] = '???';
		}
		return $result;
	}


	//unequips selected item
	public function unequip($inventoryId)
	{
		$sql = "UPDATE inventory SET equipped=null WHERE inventory_id=:id AND character_id=:charId";
		$params = array('id' => $inventoryId, 'charId' => $this->characterData->character_id);
		$this->dbClient->query($sql, $params);
	}
}
