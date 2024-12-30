<?php
//@author Krzysztof Sikorski
$cfg = require_once'./_init.php';


class Daemon_Controller_Account extends Daemon_Controller
{
	protected $pageSubtitle = 'Ustawienia';
	protected $pageSubtitleDetails = 'postać';
	protected $pageTemplatePath = 'edit-character.xml';
	protected $requireAuthentication = true;
	private $character;


	public function prepareModel()
	{
		$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
		$this->character = new Daemon_DbObject_Character;
		if($id)
		{
			$this->character->attachDbClient($this->dbClient);
			$params = array('character_id' => $id, 'player_id' => $this->player->getPlayerId());
			$this->character->get($params);
		}
		if(!$this->character->character_id)
		{
			Daemon_MsgQueue::add('Wybrana postać nie istnieje.');
			Daemon::redirect($this->cfg->getUrl('map'));
			exit;
		}
	}


	public function prepareView()
	{
		$this->pageSubtitleDetails = $this->character->name;
		$this->view->character = $this->character;
		$this->view->preview = Daemon::formatMessage($this->character->description, true);
	}


	protected function runCommands()
	{
		//update character
		if(isset($_POST['avatar'], $_POST['quote'], $_POST['desc']))
		{
			$this->character->show_player = !empty($_POST['player']);
			$this->character->avatar_url = $_POST['avatar'];
			$this->character->quote = $_POST['quote'];
			$this->character->description = $_POST['desc'];
			$this->character->put();
			return true;
		}
		return false;
	}
}


$ctrl = new Daemon_Controller_Account($cfg);
$ctrl->execute();
