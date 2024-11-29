<?php
//@author Krzysztof Sikorski
class Daemon_Item_Heal extends Daemon_ItemInterface
{


	public function execute($inventoryId, $params)
	{
		$msg = '';
		$char = $this->characterData;
		$params = explode(',', $params);
		$deltaHealth = isset($params[0]) ? (int) $params[0] : 0;
		$deltaMana = isset($params[1]) ? (int) $params[1] : 0;
		if($deltaHealth)
		{
			$oldValue = $char->health;
			$char->health = min($char->health_max, $char->health + $deltaHealth);
			$deltaHealth = $char->health - $oldValue;
			$msg[] = "zdrowie: +$deltaHealth";
		}
		if($deltaMana)
		{
			$oldValue = $char->mana;
			$char->mana = min($char->mana_max, $char->mana + $deltaMana);
			$deltaMana = $char->mana - $oldValue;
			$msg[] = "mana: +$deltaMana";
		}
		$char->put();
		$this->deleteItem($inventoryId);
		return implode(', ', $msg);
	}
}
