<?php
//@author Krzysztof Sikorski
$cfg = require_once'./_init.php';


class Daemon_Controller_Character extends Daemon_Controller
{
	protected $pageSubtitle = 'Postać';
	protected $pageTemplatePath = 'character.xml';
	protected $requireActiveChar = true;
	protected $requireAuthentication = true;
	protected $requireNoEvents = true;
	private $eventLog;


	public function prepareView()
	{
		if($this->eventLog)
		{
			$this->pageSubtitle = 'Użycie zaklęcia';
			$this->pageTemplatePath = 'event.xml';
			$this->view->eventLog = $this->eventLog;
			return;
		}
		//prepare attributes list
		$list = array();
		foreach(Daemon_Dictionary::$characterAttributes as $key => $name)
		{
			$col = "a_$key";
			$value = $this->characterData->$col;
			$list[$key] = array('name' => $name, 'value' => $value, 'inc' => ($value <= $this->characterData->xp_free));
		}
		$this->view->attributes = $list;
		//prepare skill list
		$list = array();
		foreach(Daemon_Dictionary::$characterSkills as $key => $name)
		{
			$col = "s_$key";
			$value = $this->characterData->$col;
			$list[$key] = array('name' => $name, 'value' => $value, 'inc' => ($value <= $this->characterData->xp_free));
		}
		$this->view->skills = $list;
		//prepare spell list
		$this->view->spells = $this->characterData->getSpells();
		//prepare combat stats
		$unit = (array) $this->characterData->getCombatUnit();
		$attackTypes = Daemon_Dictionary::$combatAttackTypes;
		$attackSpecials = Daemon_Dictionary::$combatAttackSpecials;
		$armorSpecials = Daemon_Dictionary::$combatArmorSpecials;
		$unit['type1_name'] = $unit['type1'] ? $attackTypes[$unit['type1']] : null;
		$unit['type2_name'] = $unit['type2'] ? $attackTypes[$unit['type2']] : null;
		$unit['sp1_name'] = $unit['sp1_type'] ? $attackSpecials[$unit['sp1_type']] : null;
		$unit['sp2_name'] = $unit['sp2_type'] ? $attackSpecials[$unit['sp2_type']] : null;
		$unit['armor_sp_name'] = $unit['armor_sp_type'] ? $armorSpecials[$unit['armor_sp_type']] : null;
		$this->view->combatStats = $unit;
	}


	protected function runCommands()
	{
		//improve attribute
		if(isset($_POST['incA']))
		{
			$this->characterData->improveAttribute($_POST['incA']);
			return true;
		}
		//improve skill
		if(isset($_POST['incS']))
		{
			$this->characterData->improveSkill($_POST['incS']);
			return true;
		}
		//cast spell
		if(isset($_POST['cast']))
		{
			$handler = new Daemon_Spell();
			$handler->attachCharacterData($this->characterData);
			$handler->attachDbClient($this->dbClient);
			$handler->execute($this->view, $_POST['cast']);
			$this->eventLog = $handler->getUsageLog();
			return true;
		}
		return false;
	}
}


$ctrl = new Daemon_Controller_Character($cfg);
$ctrl->execute();
