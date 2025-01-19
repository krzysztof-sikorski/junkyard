<?php
//@author Krzysztof Sikorski
$cfg = require_once'./_init.php';


class Daemon_Controller_Register extends Daemon_Controller
{
	protected $pageSubtitle = 'Rejestracja';
	protected $pageTemplatePath = 'register.xml';
	private $registerEnabled;


	public function prepareModel()
	{
		$this->registerEnabled = (bool) $this->dbCfg->registerEnabled;
	}


	public function prepareView()
	{
		$this->view->registerEnabled = $this->registerEnabled;
	}


	protected function runCommands()
	{
		if(!$this->registerEnabled)
			Daemon_MsgQueue::add('Rejestracja wyłączona.');
		elseif(isset($_POST['login'], $_POST['pass'], $_POST['pass2']))
		{
			$this->player->register($_POST['login'], $_POST['pass'], $_POST['pass2']);
			return true;
		}
		return false;
	}
}


$ctrl = new Daemon_Controller_Register($cfg);
$ctrl->execute();
