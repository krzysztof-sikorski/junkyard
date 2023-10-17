<?php
//@author Krzysztof Sikorski
$cfg = require_once'./_init.php';


class Daemon_Controller_Page extends Daemon_Controller
{
	protected $pageSubtitle = 'Klany';
	protected $pageSubtitleDetails = null;
	protected $pageTemplatePath = 'stats-clans.xml';
	private $listLimit;
	private $stats;


	public function prepareModel()
	{
		$this->stats = new Daemon_Statistics($this->dbClient);
		$this->listLimit = max(1, (int) $this->dbCfg->listLimitStatistics);
	}


	public function prepareView()
	{
		$clanId = isset($_GET['view']) ? $_GET['view'] : null;
		if($clanId)
			$this->prepareViewClan($clanId);
		else $this->prepareViewList();
	}


	//single duel mode
	private function prepareViewClan($clanId)
	{
		$clan = $this->stats->getClanById($clanId);
		if ($clan)
			$clan['description'] = Daemon::formatMessage($clan['description'], true);
		$this->pageSubtitle = 'Klan';
		$this->pageTemplatePath = 'stats-clan.xml';
		$this->pageSubtitleDetails = isset($clan['name']) ? $clan['name'] : null;
		$this->view->menu = $this->view->getStatisticsMenu();
		$this->view->clan = $clan;
	}


	//character list mode
	private function prepareViewList()
	{
		$this->pageSubtitleUseQuery = true;
		$from = isset($_GET['from']) ? $_GET['from'] : null;
		$data = $this->stats->getClans($this->listLimit, $from);
		$nextUrl = $data['next'] ? '?from='.urlencode($data['next']) : null;
		$this->view->menu = $this->view->getStatisticsMenu('clans');
		$this->view->nextUrl = $nextUrl;
		$this->view->list = $data['list'];
	}
}


$ctrl = new Daemon_Controller_Page($cfg);
$ctrl->execute();
