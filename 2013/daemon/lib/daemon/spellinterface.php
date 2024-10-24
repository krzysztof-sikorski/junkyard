<?php
//@author Krzysztof Sikorski
abstract class Daemon_SpellInterface
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


	abstract public function execute($spellId, $cost);


	final public function updateCharacterMana($cost)
	{
		if($this->characterData->mana < $cost)
		{
			Daemon_MsgQueue::add("Koszt $cost - nie masz tyle many.");
			return false;
		}
		$this->characterData->mana -= $cost;
		return true;
	}
}
