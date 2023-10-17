<?php
//@author Krzysztof Sikorski
$cfg = require_once'./_init.php';


class Daemon_Controller_Page extends Daemon_Controller
{
	protected $pageSubtitle = 'Ustawienia';
	protected $pageSubtitleDetails = 'konto';
	protected $pageTemplatePath = 'edit-account.xml';
	protected $requireAuthentication = true;


	public function prepareView()
	{
		$this->view->player = $this->player;
		$this->view->skins = array_keys(Daemon_Dictionary::$skinDirUrls);
	}


	protected function runCommands()
	{
		//update player data
		if(isset($_POST['name'], $_POST['pass1'], $_POST['pass2'], $_POST['skin'], $_POST['email']))
		{
			if($_POST['pass1'] || $_POST['pass2'])
				$this->player->setPassword($_POST['pass1'], $_POST['pass2']);
			$_POST['name'] = Daemon::normalizeString($_POST['name']);
			$this->player->name = $_POST['name'] ? $_POST['name'] : null;
			$this->player->skin = $_POST['skin'] ? $_POST['skin'] : null;
			$this->player->email = $_POST['email'] ? $_POST['email'] : null;
			$this->player->put();
			return true;
		}
		return false;
	}
}


$ctrl = new Daemon_Controller_Page($cfg);
$ctrl->execute();
