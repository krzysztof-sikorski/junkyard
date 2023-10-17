<?php
//@author Krzysztof Sikorski
$cfg = require_once'./_init.php';


class Daemon_Controller_Characters extends Daemon_Controller
{
	protected $pageSubtitle = 'Postacie';
	protected $pageSubtitleDetails = null;
	protected $pageTemplatePath = 'stats-characters.xml';
	private $listLimit;
	private $stats;
	private $clanId;


	public function prepareModel()
	{
		$this->clanId = isset($_GET['clan']) ? (string) $_GET['clan'] : null;
		$this->stats = new Daemon_Statistics($this->dbClient);
		$this->listLimit = max(1, (int) $this->dbCfg->listLimitStatistics);
	}


	public function prepareView()
	{
		$characterId = isset($_GET['view']) ? (int) $_GET['view'] : 0;
		if($characterId)
			$this->prepareViewCharacter($characterId);
		else $this->prepareViewList();
	}


	//single character mode
	private function prepareViewCharacter($characterId)
	{
		$character = $this->stats->getCharacterById($characterId);
		$this->pageTemplatePath = 'stats-character.xml';
		$this->pageSubtitleDetails = isset($character['name']) ? $character['name'] : null;
		$this->view->menu = $this->view->getStatisticsMenu();
		if($character)
		{
			$character['description'] = Daemon::formatMessage($character['description'], true);
			$this->view->genderName = Daemon_Dictionary::$genders[(string) $character['gender']];
			$playerId = $this->player->getPlayerId();
			if($playerId && ($character['player_id'] != $playerId))
				$this->view->mailUrl = sprintf('mail?to=%s', urlencode($character['name']));
			else $this->view->mailUrl = null;
		}
		$this->view->characterId = $characterId;
		$this->view->character = $character;
	}


	//character list mode
	private function prepareViewList()
	{
		$this->pageSubtitleUseQuery = true;
		$listOrder = isset($_GET['sort']) ? (string) $_GET['sort'] : 'xp';

		$from = isset($_GET['from']) ? (string) $_GET['from'] : null;
		$data = $this->stats->getCharacters($this->listLimit, $from, $listOrder, $this->clanId);

		if($data['next'] )
		{
			$nextUrl = '?from='.urlencode($data['next']);
			if($this->clanId)
				$nextUrl .= '&clan='.urlencode($this->clanId);
			if($listOrder)
				$nextUrl .= '&sort='.urlencode($listOrder);
		}
		else $nextUrl = null;
		$this->view->menu = $this->view->getStatisticsMenu('characters');
		$this->view->nextUrl = $nextUrl;
		$this->view->list = $data['list'];

		$headers = array(
			'name' => array('name' => 'Postać'),
			'lvl' => array('name' => 'Poziom'),
			'xp' => array('name' => 'Doświadczenie', 'abbr' => 'EXP'),
			'fac' => array('name' => 'Frakcja'),
			'clan' => array('name' => 'Klan'),
			'date' => array('name' => 'Data narodzin'),
			'last' => array('name' => 'Ostatnia wizyta'),
			'win' => array('name' => 'Wygrane pojedynki', 'abbr' => 'Win'),
			'los' => array('name' => 'Przegrane pojedynki', 'abbr' => 'Los'),
		);
		$headers[$listOrder]['selected'] = true;
		$this->view->headers = $headers;
	}
}


$ctrl = new Daemon_Controller_Characters($cfg);
$ctrl->execute();
