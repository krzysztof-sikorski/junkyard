<?php
//@author Krzysztof Sikorski
class Daemon_Spell_Identify extends Daemon_SpellInterface
{

	public function execute($spellId, $cost)
	{
		if(!$this->updateCharacterMana($cost))
			return null;
		$sql = "UPDATE inventory SET flags=CONCAT(flags, ',identified') WHERE character_id=:id AND status!='storage'";
		$this->dbClient->query($sql, array('id' => $this->characterData->character_id));
		Daemon_MsgQueue::add('Zawartość plecaka została zidentyfikowana.');
		return null;
	}
}
