<?php
//@author Krzysztof Sikorski
$cfg = require_once'./_init.php';


class Daemon_Controller_Page extends Daemon_Controller
{
	protected $pageSubtitle = 'Reset hasła';
	protected $pageTemplatePath = 'reset-password.xml';
	private $registerEnabled;


	public function prepareModel()
	{
		$this->registerEnabled = (bool) $this->dbCfg->registerEnabled;
	}


	protected function runCommands()
	{
		if(isset($_POST['login'], $_POST['email'], $_POST['pass'], $_POST['pass2']))
		{
			$this->player->preparePasswordReset($_POST['login'], $_POST['email'], $_POST['pass'], $_POST['pass2']);
			return true;
		}
		elseif (isset($_GET['key']))
		{
			$this->player->resetPassword($_GET['key']);
			return true;
		}
		return false;
	}

}


$ctrl = new Daemon_Controller_Page($cfg);
$ctrl->execute();
