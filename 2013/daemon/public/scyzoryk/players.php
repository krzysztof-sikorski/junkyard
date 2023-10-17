<?php
//@author Krzysztof Sikorski
$cfg = require_once'../_init.php';


class Daemon_Scyzoryk_Controller_Page extends Daemon_Scyzoryk_Controller
{
	protected $pageSubtitle = 'Gracze';
	protected $pageTemplatePath = 'scyzoryk/players.xml';


	protected function prepareView()
	{
		$sql = "SELECT p.player_id, p.login, p.date_created, p.last_login, p.roles, c.characters
			FROM players p
			JOIN (
				SELECT player_id, GROUP_CONCAT(name SEPARATOR '\\n') AS characters
				FROM characters
				GROUP BY player_id
			) c ON c.player_id = p.player_id
			ORDER BY p.player_id";
		$data = $this->dbClient->selectAll($sql);
		foreach ($data as &$row)
			$row['characters'] = explode("\n", $row['characters']);
		$this->view->rows = $data;
	}
}


$ctrl = new Daemon_Scyzoryk_Controller_Page($cfg);
$ctrl->execute();
