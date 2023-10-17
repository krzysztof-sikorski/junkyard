<?php
//@author Krzysztof Sikorski
$cfg = require_once'./_init.php';


class Daemon_Controller_Duel extends Daemon_Controller
{
	protected $pageSubtitle = 'Pojedynek';
	protected $pageTemplatePath = 'duel.xml';
	protected $requireActiveChar = true;
	protected $requireAuthentication = true;
	protected $requireLocation = true;
	protected $requireNoEvents = true;
	private $combatLog = null;


	public function prepareView()
	{
		$this->view->combatLog = $this->combatLog;
	}


	public function runCommands()
	{
		//attack
		if(isset($_POST['attack']))
		{
			$this->view->setGameHeader($this->player->getPlayerId(),
				$this->activeCharacter, $this->characterData, $this->location);
			$this->combatLog = $this->characterData->attack($this->view, $_POST['attack'], $this->location->type);
			return true;
		}
		return false;
	}
}


$ctrl = new Daemon_Controller_Duel($cfg);
$ctrl->execute();
