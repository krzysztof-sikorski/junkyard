<?php
//@author Krzysztof Sikorski
$cfg = require_once'../_init.php';


class Daemon_Scyzoryk_Controller_MonsterDropEdit extends Daemon_Scyzoryk_Controller
{
	protected $pageSubtitle = 'Potwory';
	protected $pageTemplatePath = 'scyzoryk/monster-drop-edit.xml';
	private $drop;


	protected function prepareModel()
	{
		$this->drop = $this->editor->selectRow('Daemon_Scyzoryk_DbRowMonsterDrop', $this->editId, $this->editId2);
	}


	protected function prepareView()
	{
		$this->pageSubtitleDetails = $this->drop ? 'edycja dropu' : null;
		$this->view->drop = $this->drop;
	}


	protected function runCommands()
	{
		if(is_null($this->drop))
			return false;
		if(isset($_POST['chance']))
		{
			$this->drop->chance = $_POST['chance'];
			$this->editor->updateRow($this->drop);
			return true;
		}
		return false;
	}
}


$ctrl = new Daemon_Scyzoryk_Controller_MonsterDropEdit($cfg);
$ctrl->execute();
