<?php
//@author Krzysztof Sikorski
$cfg = require_once'./_init.php';


class Daemon_Controller_Page extends Daemon_Controller
{
	protected $pageSubtitle = 'Bitwy';
	protected $pageSubtitleDetails = null;
	protected $pageTemplatePath = 'stats-battles.xml';
	private $listLimit;
	private $stats;


	public function prepareModel()
	{
		$this->stats = new Daemon_Statistics($this->dbClient);
		$this->listLimit = max(1, (int) $this->dbCfg->listLimitStatistics);
	}


	public function prepareView()
	{
		$battleId = isset($_GET['view']) ? (int) $_GET['view'] : 0;
		if($battleId)
			$this->prepareViewBattle($battleId);
		else $this->prepareViewList();
	}


	//single item mode
	private function prepareViewBattle($battleId)
	{
		$this->pageSubtitle = 'Bitwa';
		$this->pageTemplatePath = 'stats-battle.xml';
		$this->view->menu = $this->view->getStatisticsMenu();
		$this->view->battle = $this->stats->getBattleById($battleId);
	}


	//character list mode
	private function prepareViewList()
	{
		$this->pageSubtitleUseQuery = true;
		$from = isset($_GET['from']) ? (int) $_GET['from'] : 0;
		$data = $this->stats->getBattles($this->listLimit, $from);
		$nextUrl = $data['next'] ? '?from='.urlencode($data['next']) : null;
		$this->view->menu = $this->view->getStatisticsMenu('battles');
		$this->view->nextUrl = $nextUrl;
		$this->view->list = $data['list'];
	}
}


$ctrl = new Daemon_Controller_Page($cfg);
$ctrl->execute();
