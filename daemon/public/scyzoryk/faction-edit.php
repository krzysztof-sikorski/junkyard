<?php
//@author Krzysztof Sikorski
$cfg = require_once'../_init.php';


class Daemon_Scyzoryk_Controller_FactionEdit extends Daemon_Scyzoryk_Controller
{
	protected $pageSubtitle = 'Frakcje';
	protected $pageTemplatePath = 'scyzoryk/faction-edit.xml';
	private $faction;


	protected function prepareModel()
	{
		$this->faction = $this->editor->selectRow('Daemon_Scyzoryk_DbRowFaction', $this->editId);
	}


	protected function prepareView()
	{
		$this->pageSubtitleDetails = $this->faction ? $this->faction->name : null;
		$this->view->faction = $this->faction;
		$this->view->factionRanks = $this->browser->getFactionRanks($this->editId);
		$this->view->titles = $this->browser->getTitles();
	}


	protected function runCommands()
	{
		if(is_null($this->faction))
			return false;
		if(isset($_POST['id'], $_POST['name']) && $_POST['id'])
		{
			$this->faction->faction_id = $_POST['id'];
			$this->faction->name = $_POST['name'];
			$this->faction->power = $_POST['power'];
			$this->editor->updateRow($this->faction);
			return true;
		}
		//add rank
		if(isset($_POST['addRank'], $_POST['id']) && $_POST['id'])
		{
			$params = array(
				'faction_id' => $this->editId,
				'rank_id' => (int) $_POST['id'],
				'min_points' => (int) Daemon::getArrayValue($_POST, 'min_points'),
				'title_id' => Daemon::getArrayValue($_POST, 'title_id'),
				);
			$row = new Daemon_Scyzoryk_DbRowFactionRank($params);
			$this->editor->updateRow($row);
			return true;
		}
		//delete ranks
		if(isset($_POST['del']))
		{
			$this->editor->deleteFactionRanks($this->editId, $_POST['del']);
			return true;
		}
		return false;
	}
}


$ctrl = new Daemon_Scyzoryk_Controller_FactionEdit($cfg);
$ctrl->execute();
