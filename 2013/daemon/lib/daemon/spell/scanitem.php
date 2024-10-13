<?php
//@author Krzysztof Sikorski
class Daemon_Spell_ScanItem extends Daemon_SpellInterface
{
	public function execute($spellId, $cost)
	{
		$result = null;
		$target = isset($_POST['target']) ? $_POST['target'] : null;
		if($target)
		{
			if(!$this->updateCharacterMana($cost))
				return null;
			$item = $this->getItemByName($target);
			if($item->item_id)
			{
				$result['item'] = $item;
				$result['typeName'] = Daemon::getArrayValue(Daemon_Dictionary::$itemTypes, $item->type);
				$result['damageType'] = Daemon::getArrayValue(Daemon_Dictionary::$itemDamageTypes, $item->damage_type);
				$result['shops'] = $this->getShopsByItemId($result['item']->item_id);
				$result['monsters'] = $this->getMonstersByItemId($result['item']->item_id);
			}
			else
			{
				Daemon_MsgQueue::add('Dziwne... zaklęcie niczego nie wskazuje...');
				$result = null;
			}
		}
		$this->view->spellId = $spellId;
		$this->view->cost = $cost;
		$this->view->target = $target;
		$this->view->result = $result;
		ob_start();
		$this->view->display('spell/scanitem.xml');
		return ob_get_clean();
	}


	private function getItemByName($name)
	{
		$item = new Daemon_DbObject_Item();
		$item->attachDbClient($this->dbClient);
		$item->get(array('name' => $name), true);
		return $item;
	}


	private function getMonstersByItemId($itemId)
	{
		$sql = "SELECT m.name, m.level FROM monster_drops md JOIN monsters m USING(monster_id)
			WHERE md.item_id=:id ORDER BY m.name";
		$data = $this->dbClient->selectAll($sql, array('id' => $itemId));
		$result = array();
		foreach($data as $row)
			$result[] = sprintf('%s (poziom %d)', $row['name'], $row['level']);
		return $result;
	}


	private function getShopsByItemId($itemId)
	{
		$sql = "SELECT s.name FROM service_items si JOIN services s USING(service_id)
			WHERE si.item_id=:id ORDER BY s.name";
		return $this->dbClient->selectColumn($sql, array('id' => $itemId));
	}
}
