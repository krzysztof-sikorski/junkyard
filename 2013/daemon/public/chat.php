<?php
//@author Krzysztof Sikorski
$cfg = require_once'./_init.php';


class Daemon_Controller_Chat extends Daemon_Controller
{
	protected $pageSubtitle = 'Ogłoszenia';
	protected $pageTemplatePath = 'chat.xml';
	private $channelId;
	private $channels;
	private $forum;
	private $writeAccess;


	public function prepareModel()
	{
		$this->forum = new Daemon_Forum($this->dbClient);
		$this->channels = $this->activeCharacter->getForumChannels();
		$this->channelId = isset($_GET['v']) ? $_GET['v'] : null;
		if(!isset($this->channels[$this->channelId]))
			$this->channelId = 'public';
		$this->writeAccess = !empty($this->channels[$this->channelId]['writable']);
	}


	public function prepareView()
	{
		$listLimit = max(1, (int) $this->dbCfg->listLimitMessages);
		$listOffset = isset($_GET['n']) ? (int) $_GET['n'] : 0;

		$this->pageSubtitleUseQuery = true;
		$from = isset($_GET['from']) ? (int) $_GET['from'] : 0;
		$data = $this->forum->getChat($listLimit, $from, $this->channelId);

		foreach($data['list'] as &$row)
			$row['content'] = Daemon::formatMessage($row['content'], true);
		$this->view->inputMsg = isset($_POST['msg']) ? $_POST['msg'] : null;
		$this->view->list = $data['list'];

		$this->view->menu = $this->view->getChatMenu($this->channels, $this->channelId);
		$this->view->nextUrl = $data['next'] ? '?from='.urlencode($data['next']) : null;
		$this->view->channelId = $this->channelId;
		$this->view->writeAccess = $this->writeAccess;
	}


	protected function runCommands()
	{
		if($this->writeAccess && isset($_POST['msg']))
		{
			$this->forum->addChat($this->player->getCharacterId(), $this->channelId, $_POST['msg']);
			return true;
		}
		return false;
	}
}


$ctrl = new Daemon_Controller_Chat($cfg);
$ctrl->execute();
