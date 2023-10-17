<?php
//@author Krzysztof Sikorski
$cfg = require_once'../_init.php';


class Daemon_Scyzoryk_Controller_News extends Daemon_Scyzoryk_Controller
{
	protected $pageSubtitle = 'News';
	protected $pageTemplatePath = 'scyzoryk/news.xml';
	private $news;


	protected function prepareModel()
	{
		$this->news = new Daemon_News($this->dbClient);
	}


	protected function prepareView()
	{
		$this->view->entries = $this->news->getEntries(null, false);
	}


	protected function runCommands()
	{
		//delete entry
		if(isset($_POST['del']))
		{
			$this->news->deleteEntry($_POST['del']);
			return true;
		}
		//add entry
		if(isset($_POST['id'], $_POST['title'], $_POST['author'], $_POST['content']))
		{
			if(!$_POST['id'])
				$_POST['id'] = $this->news->generateId(getenv('SERVER_NAME'), $_POST['title']);
			$this->news->updateEntry($_POST['id'], $_POST['title'], $_POST['author'], $_POST['content']);
			return true;
		}
		return false;
	}
}


$ctrl = new Daemon_Scyzoryk_Controller_News($cfg);
$ctrl->execute();
