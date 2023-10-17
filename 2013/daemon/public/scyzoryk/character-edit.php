<?php
//@author Krzysztof Sikorski
$cfg = require_once'../_init.php';


class Daemon_Scyzoryk_Controller_Page extends Daemon_Scyzoryk_Controller
{
	protected $pageSubtitle = 'Postacie';
	protected $pageTemplatePath = 'scyzoryk/character-edit.xml';
	private $character;
	private $characterData;


	protected function prepareModel()
	{
		$this->character = new Daemon_DbObject_Character();
		$this->character->attachDbClient($this->dbClient);
		$this->character->get(array('character_id' => $this->editId));
		if (empty($this->character->character_id))
		{
			Daemon_MsgQueue::add('Wybrana postać nie istnieje.');
			Daemon::redirect($this->cfg->getUrl('scyzoryk/characters'));
			exit;
		}
		$this->characterData = $this->character->getCharacterData();
	}


	protected function prepareView()
	{
		$this->pageSubtitleDetails = $this->character ? $this->character->name : null;
		$this->view->character = $this->character;
		$this->view->characterData = $this->characterData;
		$this->view->genders = Daemon_Dictionary::$genders;
	}


	protected function runCommands()
	{
		if(is_null($this->character))
			return false;
		if(isset($_POST['save']))
		{
			$this->character->name = Daemon::getArrayValue($_POST, 'name');
			$this->character->gender = Daemon::getArrayValue($_POST, 'gender');
			$this->character->last_action = Daemon::getArrayValue($_POST, 'last_action');
			$this->character->clan_id = Daemon::getArrayValue($_POST, 'clan_id');
			$this->character->avatar_url = Daemon::getArrayValue($_POST, 'avatar_url');
			$this->character->quote = Daemon::getArrayValue($_POST, 'quote');
			$this->character->description = Daemon::getArrayValue($_POST, 'description');
			$this->character->put();
			return true;
		}
		if(isset($_POST['saveData']))
		{
			$this->characterData->location_id = Daemon::getArrayValue($_POST, 'location_id');
			$this->characterData->faction_id = Daemon::getArrayValue($_POST, 'faction_id');
			$this->characterData->faction_points = (int) Daemon::getArrayValue($_POST, 'faction_points');
			$this->characterData->rank_id = (int) Daemon::getArrayValue($_POST, 'rank_id');
			$this->characterData->turns = (int) Daemon::getArrayValue($_POST, 'turns');
			$this->characterData->gold_purse = (int) Daemon::getArrayValue($_POST, 'gold_purse');
			$this->characterData->gold_bank = (int) Daemon::getArrayValue($_POST, 'gold_bank');
			$this->characterData->level = (int) Daemon::getArrayValue($_POST, 'level');
			$this->characterData->xp_free = (int) Daemon::getArrayValue($_POST, 'xp_free');
			$this->characterData->health = (int) Daemon::getArrayValue($_POST, 'health');
			$this->characterData->health_max = (int) Daemon::getArrayValue($_POST, 'health_max');
			$this->characterData->mana = (int) Daemon::getArrayValue($_POST, 'mana');
			$this->characterData->mana_max = (int) Daemon::getArrayValue($_POST, 'mana_max');
			$this->characterData->a_str = (int) Daemon::getArrayValue($_POST, 'a_str');
			$this->characterData->a_dex = (int) Daemon::getArrayValue($_POST, 'a_dex');
			$this->characterData->a_vit = (int) Daemon::getArrayValue($_POST, 'a_vit');
			$this->characterData->a_pwr = (int) Daemon::getArrayValue($_POST, 'a_pwr');
			$this->characterData->a_wil = (int) Daemon::getArrayValue($_POST, 'a_wil');
			$this->characterData->s_pstr = (int) Daemon::getArrayValue($_POST, 's_pstr');
			$this->characterData->s_patk = (int) Daemon::getArrayValue($_POST, 's_patk');
			$this->characterData->s_pdef = (int) Daemon::getArrayValue($_POST, 's_pdef');
			$this->characterData->s_pres = (int) Daemon::getArrayValue($_POST, 's_pres');
			$this->characterData->s_preg = (int) Daemon::getArrayValue($_POST, 's_preg');
			$this->characterData->s_mstr = (int) Daemon::getArrayValue($_POST, 's_mstr');
			$this->characterData->s_matk = (int) Daemon::getArrayValue($_POST, 's_matk');
			$this->characterData->s_mdef = (int) Daemon::getArrayValue($_POST, 's_mdef');
			$this->characterData->s_mres = (int) Daemon::getArrayValue($_POST, 's_mres');
			$this->characterData->s_mreg = (int) Daemon::getArrayValue($_POST, 's_mreg');
			$this->characterData->sp_scout = (int) Daemon::getArrayValue($_POST, 'sp_scout');
			$this->characterData->sp_identify = (int) Daemon::getArrayValue($_POST, 'sp_identify');
			$this->characterData->sp_vchar = (int) Daemon::getArrayValue($_POST, 'sp_vchar');
			$this->characterData->sp_vmonster = (int) Daemon::getArrayValue($_POST, 'sp_vmonster');
			$this->characterData->sp_vitem = (int) Daemon::getArrayValue($_POST, 'sp_vitem');
			$this->characterData->put();
			return true;
		}
		return false;
	}
}


$ctrl = new Daemon_Scyzoryk_Controller_Page($cfg);
$ctrl->execute();
