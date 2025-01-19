<?php
//@author Krzysztof Sikorski
$cfg = require_once'../_init.php';


class Daemon_Scyzoryk_Controller_Page extends Daemon_Scyzoryk_Controller
{
	protected $pageSubtitle = 'Postacie';
	protected $pageTemplatePath = 'scyzoryk/characters.xml';


	protected function prepareView()
	{
		$sql = "SELECT c.character_id, c.player_id, p.login AS player_login, c.name, c.last_action
			FROM characters c
			JOIN character_data cd USING(character_id)
			LEFT JOIN players p USING(player_id)
			ORDER BY c.character_id";
		$data = $this->dbClient->selectAll($sql);
/*
		foreach ($data as &$row)
			$row['characters'] = explode("\n", $row['characters']);
*/
		$this->view->rows = $data;
	}
}


$ctrl = new Daemon_Scyzoryk_Controller_Page($cfg);
$ctrl->execute();
