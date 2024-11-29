<?php
//@author Krzysztof Sikorski
class Daemon_Item_GiveSpell extends Daemon_ItemInterface
{


	public function execute($inventoryId, $spellId)
	{
		$char = $this->characterData;
		//get spell data
		$sql = "SELECT * FROM spells WHERE spell_id=:id";
		$spell = $this->dbClient->selectRow($sql, array('id' => $spellId));
		if(!$spell)
			return 'Takie zaklęcie nie istnieje.';
		//give spell to character
		$colName = "sp_$spellId";
		if(($char->$colName > 0) && ($char->$colName <= $spell['min_cost']))
			return "Zaklęcie $spell[name] znasz już na poziomie mistrzowskim.";
		if($char->$colName < 1)
		{
			$msg = "Poznajesz nowe zaklęcie: $spell[name].";
			$char->$colName = $spell['max_cost'];
		}
		elseif($char->$colName > $spell['min_cost'])
		{
			$msg = "Lepiej poznajesz zaklęcie $spell[name] - potrzebujesz mniej many by je rzucić.";
			$char->$colName -= round(($spell['max_cost'] - $spell['min_cost']) / ($spell['max_level'] - 1));
		}
		$char->put();
		$this->deleteItem($inventoryId);
		return $msg;
	}
}
