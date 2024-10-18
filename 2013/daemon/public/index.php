<?php
//@author Krzysztof Sikorski
$cfg = require_once'./_init.php';


class Daemon_Controller_Index extends Daemon_Controller
{
	protected $pageTemplatePath = 'index.xml';
	private $news;


	public function prepareModel()
	{
		$this->news = new Daemon_News($this->dbClient);
	}


	public function prepareView()
	{
		$this->view->loginEnabled = (bool) $this->dbCfg->loginEnabled;
		$this->view->news = $this->news->getEntries(3, false);
	}
}


$ctrl = new Daemon_Controller_Index($cfg);
$ctrl->execute();
