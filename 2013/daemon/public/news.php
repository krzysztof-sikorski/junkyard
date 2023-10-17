<?php
//@author Krzysztof Sikorski
$cfg = require_once'./_init.php';


class Daemon_Controller_News extends Daemon_Controller
{
	protected $disablePlayer = true;
	protected $pageOutputMode = Daemon_View::MODE_ATOM;
	protected $pageSubtitle = 'Regulamin';
	protected $pageTemplatePath = 'news.xml';
	private $news;


	public function prepareModel()
	{
		$this->news = new Daemon_News($this->dbClient);
	}


	public function prepareView()
	{
		$this->view->feedId = $this->cfg->applicationUrl;
		$this->view->feedUrl = "{$this->cfg->applicationUrl}news";
		$this->view->feedTitle = $this->cfg->applicationTitle;
		$this->view->feedUpdated = $this->news->getLastUpdated();
		$this->view->entries = $this->news->getEntries(10, true);
	}
}


$ctrl = new Daemon_Controller_News($cfg);
$ctrl->execute();
