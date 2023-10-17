<?php
//@author Krzysztof Sikorski
class Daemon_Item_Teleport extends Daemon_ItemInterface
{


	public function execute($inventoryId, $locationId)
	{
		$this->characterData->location_id = $locationId;
		$this->characterData->put();
		$this->deleteItem($inventoryId);
		return "Nagle znajdujesz się zupełnie gdzieindziej...";
	}
}

