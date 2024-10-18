<?php
//@author Krzysztof Sikorski
$cfg = require_once'../_init.php';


class Daemon_Scyzoryk_Controller_Page extends Daemon_Scyzoryk_Controller
{
	protected $pageSubtitle = 'Armageddon';
	protected $pageTemplatePath = 'scyzoryk/reset.xml';
	private $dbCfg;


	protected function prepareModel()
	{
		$this->dbCfg = new Daemon_DbConfig($this->dbClient);
	}


	private function resetCharacters()
	{
		$queries = array(
			"TRUNCATE TABLE inventory",
			"TRUNCATE TABLE character_data",
			"INSERT INTO character_data(character_id) SELECT character_id FROM characters",
			"DELETE FROM combat_units WHERE combat_unit_id LIKE 'character-%'",
			"TRUNCATE TABLE character_regions",
			"TRUNCATE TABLE character_missions",
			"DELETE FROM character_titles WHERE title_id NOT IN (SELECT title_id FROM titles WHERE type='special')",
			"TRUNCATE TABLE character_statistics",
			"INSERT INTO character_statistics(character_id) SELECT character_id FROM characters",
		);
		foreach ($queries as $q)
			$this->dbClient->query($q);
	}


	private function resetHistory()
	{
		$queries = array(
			"UPDATE characters SET last_mail_id = DEFAULT",
			"TRUNCATE TABLE mail",
			"TRUNCATE TABLE chat",
			"TRUNCATE TABLE duels",
			"TRUNCATE TABLE rollovers",
		);
		foreach ($queries as $q)
			$this->dbClient->query($q);
	}


	public function runCommands()
	{
		$methods = array(
			'characters' => 'resetCharacters',
			'history' => 'resetHistory',
			'items' => 'updateItems',
		);
		if (isset($_POST['reset']))
		{
			if (isset($methods[$_POST['reset']]))
			{
				$method = $methods[$_POST['reset']];
				$this->$method();
			}
			return true;
		}
	}


	private function updateItems()
	{
		$sql = "SELECT item_id FROM items";
		$ids = $this->dbClient->selectColumn($sql);
		$n = 0;
		foreach ($ids as $id)
		{
			$item = new Daemon_DbObject_Item();
			$item->attachDbClient($this->dbClient);
			$item->get(array('item_id' => $id));
			$item->validate();
			$item->updateSuggestedValue($this->dbCfg);
			$item->put();
			$n += 1;
		}
		Daemon_MsgQueue::add("Przeliczono $n przedmiotów.");
	}
}


$ctrl = new Daemon_Scyzoryk_Controller_Page($cfg);
$ctrl->execute();
