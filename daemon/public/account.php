<?php
//@author Krzysztof Sikorski
$cfg = require_once'./_init.php';


class Daemon_Controller_Account extends Daemon_Controller
{
	protected $pageSubtitle = 'Konto';
	protected $pageTemplatePath = 'account.xml';
	protected $requireAuthentication = true;
	private $characters;


	public function prepareView()
	{
		$this->characters = $this->player->getCharacters();
		//mark active character
		$activeCharId = $this->player->getCharacterId();
		if($activeCharId && isset($this->characters[$activeCharId]))
			$this->characters[$activeCharId]['active'] = true;
		else $activeCharId = null;
		//prepare view
		$this->view->characters = $this->characters;
		$this->view->genders = Daemon_Dictionary::$genders;
		$this->view->preview = Daemon::formatMessage($this->activeCharacter->description, true);
	}


	protected function runCommands()
	{
		$turnDelta = $this->dbCfg->turnDelta;
		$turnLimit = $this->dbCfg->turnLimit;
		//create character
		if(isset($_POST['newName'], $_POST['newGender']))
		{
			$this->player->addCharacter($_POST['newName'], $_POST['newGender'], $turnDelta, $turnLimit);
			return true;
		}
		//set active character
		if(isset($_POST['use']))
		{
			$this->player->setCharacterId($_POST['use']);
			$this->activeCharacter = $this->player->getActiveCharacter();
			$this->characterData = $this->activeCharacter->getCharacterData();
			$this->activeCharacter->updateLastAction();
			return true;
		}
		//reset or delete character
		if(isset($_POST['char'], $_POST['action']))
		{
			$reset = ($_POST['action'] == 'reset');
			$this->player->deleteCharacter($_POST['char'], $reset, $turnDelta, $turnLimit);
			$this->activeCharacter = $this->player->getActiveCharacter();
			$this->characterData = $this->activeCharacter->getCharacterData();
			return true;
		}
		return false;
	}
}


$ctrl = new Daemon_Controller_Account($cfg);
$ctrl->execute();
