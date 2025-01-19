<?php
//@author Krzysztof Sikorski
$cfg = require_once'./_init.php';


class Daemon_Controller_Chat extends Daemon_Controller
{
	protected $pageSubtitle = 'Poczta';
	protected $pageTemplatePath = 'mail.xml';
	protected $requireAuthentication = true;
	private $forum;


	public function prepareModel()
	{
		$this->forum = new Daemon_Forum($this->dbClient);
		if(isset($_GET['to']))
			$_POST['to'] = $_GET['to'];
	}


	public function prepareView()
	{
		//fetch mail
		$listLimit = max(1, (int) $this->dbCfg->listLimitMessages);
		$listOffset = isset($_GET['n']) ? (int) $_GET['n'] : 0;
		$characterId = (int) $this->activeCharacter->character_id;

		$this->pageSubtitleUseQuery = true;
		$from = isset($_GET['from']) ? (int) $_GET['from'] : 0;
		$data = $this->forum->getMail($listLimit, $from, $characterId);
		foreach($data['list'] as &$row)
			$row['content'] = Daemon::formatMessage($row['content'], true);

		//mark as read
		if($data['list'])
		{
			$messageId = $data['list'][0]['message_id'];
			$sql = "UPDATE characters SET last_mail_id = :messageId WHERE character_id = :characterId";
			$params = array('characterId' => $characterId, 'messageId' => $messageId);
			$this->dbClient->query($sql, $params);
			$this->activeCharacter->last_mail_id = $messageId;
		}

		//display page
		$this->view->inputTo = isset($_POST['to']) ? $_POST['to'] : null;
		$this->view->inputMsg = isset($_POST['msg']) ? $_POST['msg'] : null;
		$this->view->list = $data['list'];
		$nextUrl = $data['next'] ? '?from='.urlencode($data['next']) : null;
		$this->view->nextUrl = $nextUrl;
	}


	protected function runCommands()
	{
		if(isset($_POST['to'], $_POST['msg']))
		{
			$this->forum->addMail($this->player->getCharacterId(), $_POST['to'], $_POST['msg']);
			return true;
		}
		return false;
	}
}


$ctrl = new Daemon_Controller_Chat($cfg);
$ctrl->execute();
