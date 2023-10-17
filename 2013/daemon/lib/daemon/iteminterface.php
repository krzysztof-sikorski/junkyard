<?php
//@author Krzysztof Sikorski
abstract class Daemon_ItemInterface
{
	protected $characterData;
	protected $dbClient;
	protected $view;


	final public function __construct(Daemon_DbClient $dbClient,
		Daemon_DbObject_CharacterData $characterData, Daemon_View $view)
	{
		$this->dbClient = $dbClient;
		$this->characterData = $characterData;
		$this->view = $view;
	}


	final protected function deleteItem($inventoryId)
	{
		$sql = "DELETE FROM inventory WHERE character_id=:charId AND inventory_id=:id";
		$params = array('charId' => $this->characterData->character_id, 'id' => $inventoryId);
		$this->dbClient->query($sql, $params);
	}


	abstract public function execute($inventoryId, $params);
}
