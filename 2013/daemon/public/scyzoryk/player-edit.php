<?php
//@author Krzysztof Sikorski
$cfg = require_once'../_init.php';


class Daemon_Scyzoryk_Controller_Page extends Daemon_Scyzoryk_Controller
{
	protected $pageSubtitle = 'Gracze';
	protected $pageTemplatePath = 'scyzoryk/player-edit.xml';
	private $player;
	private $playerRoles;


	protected function prepareModel()
	{
		$this->player = new Daemon_DbObject_Player($this->dbClient);
		$this->player->get(array('player_id' => $this->editId));
		if (empty($this->player->login))
		{
			Daemon_MsgQueue::add('Wybrany gracz nie istnieje.');
			Daemon::redirect($this->cfg->getUrl('scyzoryk/players'));
			exit;
		}
		//player roles
		$this->playerRoles = array('chat' => false, 'login' => false);
		foreach (explode(',', $this->player->roles) as $key)
			$this->playerRoles[$key] = true;
	}


	protected function prepareView()
	{
		$this->pageSubtitleDetails = $this->player ? $this->player->login : null;
		$this->view->player = $this->player;
		$this->view->skins = array_keys(Daemon_Dictionary::$skinDirUrls);
		$this->view->playerRoles = $this->playerRoles;
	}


	protected function runCommands()
	{
		if(is_null($this->player))
			return false;
		if(isset($_POST['save']))
		{
			$this->player->date_created = Daemon::getArrayValue($_POST, 'date_created');
			$this->player->last_login = Daemon::getArrayValue($_POST, 'last_login');
			$this->player->name = Daemon::getArrayValue($_POST, 'name');
			$this->player->skin = Daemon::getArrayValue($_POST, 'skin');
			$this->player->email = Daemon::getArrayValue($_POST, 'email');
			foreach ($this->playerRoles as $key => &$val)
				$val = isset($_POST['roles'][$key]);
			$this->player->roles = implode(',', array_keys(array_filter($this->playerRoles)));
			$this->player->put();
			if(!empty($_POST['pass1']) || !empty($_POST['pass2']))
				$this->player->setPassword($_POST['pass1'], $_POST['pass2']);
			return true;
		}
		return false;
	}
}


$ctrl = new Daemon_Scyzoryk_Controller_Page($cfg);
$ctrl->execute();
