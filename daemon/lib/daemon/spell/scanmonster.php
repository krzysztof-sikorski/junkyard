<?php
//@author Krzysztof Sikorski
class Daemon_Spell_ScanMonster extends Daemon_SpellInterface
{
	public function execute($spellId, $cost)
	{
		$result = null;
		$target = isset($_POST['target']) ? $_POST['target'] : null;
		if($target)
		{
			if(!$this->updateCharacterMana($cost))
				return null;
			$result['monster'] = $this->getMonsterByName($target);
			if($result['monster']->monster_id)
			{
				if($result['monster']->level)
					$chance = $this->characterData->level / ($this->characterData->level + $result['monster']->level);
				else $chance = 0;
				if(mt_rand(0, 255) < 256 * $chance)
				{
					$result['title'] = $this->getTitleById($result['monster']->title_id);
					$result['items'] = $this->getItemsByMonsterId($result['monster']->monster_id);
					$result['locations'] = $this->getLocationsByMonsterId($result['monster']->monster_id);
					$result['className'] = Daemon_Dictionary::$monsterClasses[$result['monster']->class];
					//prepare combat unit
					$unit = (array) $result['monster']->getCombatUnit(false);
					$attackTypes = Daemon_Dictionary::$combatAttackTypes;
					$attackSpecials = Daemon_Dictionary::$combatAttackSpecials;
					$armorSpecials = Daemon_Dictionary::$combatArmorSpecials;
					$unit['type1_name'] = $unit['type1'] ? $attackTypes[$unit['type1']] : null;
					$unit['type2_name'] = $unit['type2'] ? $attackTypes[$unit['type2']] : null;
					$unit['sp1_name'] = $unit['sp1_type'] ? $attackSpecials[$unit['sp1_type']] : null;
					$unit['sp2_name'] = $unit['sp2_type'] ? $attackSpecials[$unit['sp2_type']] : null;
					$unit['armor_sp_name'] = $unit['armor_sp_type'] ? $armorSpecials[$unit['armor_sp_type']] : null;
					$result['unit'] = $unit;
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
		$this->view->display('spell/scanmonster.xml');
		return ob_get_clean();
	}


	private function getItemsByMonsterId($monsterId)
	{
		$sql = "SELECT i.name, md.chance FROM monster_drops md JOIN items i USING(item_id)
			WHERE md.monster_id=:id ORDER BY i.name";
		$data = $this->dbClient->selectAll($sql, array('id' => $monsterId));
		$result = array();
		foreach($data as $row)
			$result[] = sprintf('%s (częstość %d)', $row['name'], $row['chance']);
		return $result;
	}


	private function getLocationsByMonsterId($monsterId)
	{
		$sql = "SELECT l.name FROM location_monsters lm JOIN locations l USING(location_id)
			WHERE lm.monster_id=:id ORDER BY l.name";
		return $this->dbClient->selectColumn($sql, array('id' => $monsterId));
	}


	private function getMonsterByName($name)
	{
		$monster = new Daemon_DbObject_Monster();
		$monster->attachDbClient($this->dbClient);
		$monster->get(array('name' => $name), true);
		return $monster;
	}


	private function getTitleById($titleId)
	{
		$sql = "SELECT name_f, name_m, name_n FROM titles WHERE title_id=:id";
		return $this->dbClient->selectRow($sql, array('id' => $titleId));
	}
}
