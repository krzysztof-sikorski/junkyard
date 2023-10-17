<?php
//@author Krzysztof Sikorski
$cfg = require_once'./_init.php';


class Daemon_Controller_Auth extends Daemon_Controller
{
	protected $disableMessages = true;


	public function prepareModel()
	{
	}


	public function prepareView()
	{
		$url = $this->cfg->applicationUrl;
		if($this->player->getPlayerId())
			$url .= 'account';
		Daemon::redirect($url);
		exit;
	}


	protected function runCommands()
	{
		if(!$this->dbCfg->loginEnabled)
			return false;
		if(isset($_POST['login'], $_POST['pass']))
		{
			$this->player->authenticate($_POST['login'], $_POST['pass']);
			return true;
		}
		if(isset($_POST['logout']))
		{
			$this->player->unauthenticate();
			return true;
		}
		return false;
	}
}


$ctrl = new Daemon_Controller_Auth($cfg);
$ctrl->execute();
