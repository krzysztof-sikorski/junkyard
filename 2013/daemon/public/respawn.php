<?php
//@author Krzysztof Sikorski
$cfg = require_once'./_init.php';


class Daemon_Controller_Respawn extends Daemon_Controller
{
	protected $pageSubtitle = 'Otchłań Narodzin';
	protected $pageTemplatePath = 'respawn.xml';
	protected $requireActiveChar = true;
	protected $requireAuthentication = true;
	private $defaultRespawn;
	private $respawns;


	public function prepareModel()
	{
		if (empty($this->characterData->xp_used))
		{
			Daemon_MsgQueue::add('Pamiętaj by wydać startowe doświadczenie.');
			Daemon::redirect($this->cfg->getUrl('character'));
			exit;
		}
		$this->defaultRespawn = $this->dbCfg->defaultRespawn;
		$this->gender = $this->characterData->_gender;
		if(empty($this->location->location_id))
			$this->respawns = $this->characterData->getRespawns($this->defaultRespawn);
		else
		{
			Daemon_MsgQueue::add('Już posiadasz powłokę cielesną.');
			Daemon::redirect($this->cfg->getUrl('map'));
			exit;
		}
	}


	public function prepareView()
	{
		$this->view->respawns = $this->respawns;
		$this->view->firstOne = empty($this->characterData->deaths);
		$this->view->rolloversEnabled = (bool) $this->dbCfg->rolloversEnabled;
	}


	protected function runCommands()
	{
		if(isset($_POST['respawn']))
		{
			$this->characterData->respawn($_POST['respawn'], $this->defaultRespawn);
			Daemon::redirect($this->cfg->getUrl('map'));
			exit;
		}
		return false;
	}
}


$ctrl = new Daemon_Controller_Respawn($cfg);
$ctrl->execute();
