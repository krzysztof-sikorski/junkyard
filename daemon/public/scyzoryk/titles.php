<?php
//@author Krzysztof Sikorski
$cfg = require_once'../_init.php';


class Daemon_Scyzoryk_Controller_Titles extends Daemon_Scyzoryk_Controller
{
	protected $pageSubtitle = 'Tytuły';
	protected $pageTemplatePath = 'scyzoryk/titles.xml';


	protected function prepareView()
	{
		$this->view->titles = $this->browser->getTitles();
	}


	protected function runCommands()
	{
		//add new row
		if(isset($_POST['newId']) && $_POST['newId'])
		{
			$params = array('title_id' => $_POST['newId']);
			$this->editor->updateRow(new Daemon_Scyzoryk_DbRowTitle($params));
			return true;
		}
		//delete rows
		if(isset($_POST['del']))
		{
			$this->editor->deleteTitles($_POST['del']);
			return true;
		}
		return false;
	}
}


$ctrl = new Daemon_Scyzoryk_Controller_Titles($cfg);
$ctrl->execute();
