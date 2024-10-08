<?php
//@author Krzysztof Sikorski
class Daemon_Event_Intro extends Daemon_EventInterface
{


	public function execute($params)
	{
		$this->clearEvent();
		//check title
		$sql = "SELECT 1 FROM character_titles WHERE character_id=:id AND title_id='cultist'";
		$hasTitle = (bool) $this->dbClient->selectValue($sql, array('id' => $this->characterData->character_id));
		if(!$hasTitle)
		{
			//run combat
			$combat = new Daemon_MonsterCombat();
			$combat->attachCharacterData($this->characterData);
			$combat->attachDbClient($this->dbClient);
			$combat->execute($this->view, 'cultist', true);
			$combatLog = $combat->getCombatLog();
			unset($combat);
		}
		else $combatLog = null;
		//display log
		ob_start();
		$this->view->combatLog = $combatLog;
		$this->view->hasTitle = $hasTitle;
		$this->view->display('event/intro.xml');
		return ob_get_clean();
	}
}
