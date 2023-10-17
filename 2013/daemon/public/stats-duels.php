<?php
//@author Krzysztof Sikorski
$cfg = require_once'./_init.php';


class Daemon_Controller_Page extends Daemon_Controller
{
	protected $pageSubtitle = 'Pojedynki';
	protected $pageSubtitleDetails = null;
	protected $pageTemplatePath = 'stats-duels.xml';
	private $characterId;
	private $listLimit;
	private $stats;


	public function prepareModel()
	{
		$this->characterId = isset($_GET['char']) ? (int) $_GET['char'] : 0;
		$this->stats = new Daemon_Statistics($this->dbClient);
		$this->listLimit = max(1, (int) $this->dbCfg->listLimitStatistics);
	}


	public function prepareView()
	{
		$duelId = isset($_GET['view']) ? (int) $_GET['view'] : 0;
		if($duelId)
			$this->prepareViewDuel($duelId);
		else $this->prepareViewList();
	}


	//single duel mode
	private function prepareViewDuel($duelId)
	{
		$this->pageSubtitle = 'Pojedynek';
		$this->pageTemplatePath = 'stats-duel.xml';
		$this->view->menu = $this->view->getStatisticsMenu();
		$this->view->duel = $this->stats->getDuelById($this->player->getCharacterId(), $duelId);
	}


	//character list mode
	private function prepareViewList()
	{
		$this->pageSubtitleUseQuery = true;
		$from = isset($_GET['from']) ? (int) $_GET['from'] : 0;
		$data = $this->stats->getDuels($this->listLimit, $from, $this->characterId, $this->player->getCharacterId());

		$duelTypes = array('normal' => 'zwykła', 'arena' => 'sparring');
		$winnerTypes = array('a' => 'ataker', 'b' => 'obrońca');
		foreach($data['list'] as &$row)
		{
			$row['type'] = isset($duelTypes[$row['type']]) ? $duelTypes[$row['type']] : null;
			$row['winner'] = isset($winnerTypes[$row['winner']]) ? $winnerTypes[$row['winner']] : 'remis';
		}

		if($data['next'])
		{
			$nextUrl = '?from='.urlencode($data['next']);
			if($this->characterId)
				$nextUrl .= '&char='.urlencode($this->characterId);
		}
		else $nextUrl = null;
		$this->view->menu = $this->view->getStatisticsMenu('duels');
		$this->view->nextUrl = $nextUrl;
		$this->view->list = $data['list'];
	}
}


$ctrl = new Daemon_Controller_Page($cfg);
$ctrl->execute();
