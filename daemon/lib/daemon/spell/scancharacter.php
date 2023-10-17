<?php
//@author Krzysztof Sikorski
class Daemon_Spell_ScanCharacter extends Daemon_SpellInterface
{
	public function execute($spellId, $cost)
	{
		$result = null;
		$target = isset($_POST['target']) ? $_POST['target'] : null;
		if($target)
		{
			if(!$this->updateCharacterMana($cost))
				return null;
			$result['char'] = $this->getCharacterByName($target);
			if($result['char']->character_id)
			{
				$result['cdata'] = $result['char']->getCharacterData();
				if($result['cdata']->level)
					$chance = $this->characterData->level / ($this->characterData->level + $result['cdata']->level);
				else $chance = 0;
				if(mt_rand(0, 255) < 256 * $chance)
				{
					$result['equipment'] = $this->getEquipmentByCharacterId($result['char']->character_id);
					$result['locationName'] = $this->getLocationNameById($result['cdata']->location_id);
				}
				else
				{
					Daemon_MsgQueue::add('Nie udało się rzucić zaklęcia!');
					$result = null;
				}
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
		$this->view->display('spell/scancharacter.xml');
		return ob_get_clean();
	}


	private function getCharacterByName($name)
	{
		$char = new Daemon_DbObject_Character();
		$char->attachDbClient($this->dbClient);
		$char->get(array('name' => $name), true);
		return $char;
	}


	private function getEquipmentByCharacterId($charId)
	{
		$sql = "SELECT i.name FROM inventory inv JOIN items i USING(item_id)
			WHERE inv.character_id=:id AND inv.equipped IS NOT NULL ORDER BY i.name";
		return $this->dbClient->selectColumn($sql, array('id' => $charId));
	}


	private function getLocationNameById($locationId)
	{
		$sql = "SELECT name FROM locations WHERE location_id=:id";
		return $this->dbClient->selectValue($sql, array('id' => $locationId));
	}
}
