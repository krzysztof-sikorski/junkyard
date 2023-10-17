<?php
//@author Krzysztof Sikorski
$cfg = require_once'../_init.php';


class Daemon_Scyzoryk_Controller_Config extends Daemon_Scyzoryk_Controller
{
	protected $pageSubtitle = 'Ustawienia';
	protected $pageTemplatePath = 'scyzoryk/config.xml';
	private $dbCfg;


	protected function prepareModel()
	{
		$this->dbCfg = new Daemon_DbConfig($this->dbClient);
	}


	protected function prepareView()
	{
		$this->view->cfg = $this->dbCfg;
		//healer
		$healer = explode(',', $this->dbCfg->healer);
		if(!isset($healer[0], $healer[1], $healer[2], $healer[3]))
			$healer = array(null, null, null, null);
		$this->view->healer = $healer;
	}


	protected function runCommands()
	{
		if(isset($_POST['cfg']) && is_array($_POST['cfg']))
		{
			$cfg = $_POST['cfg'];
			if(isset($_POST['healer']) && is_array($_POST['healer']))
				$cfg['healer'] = implode(',', $_POST['healer']);
			else $cfg['healer'] = '1,1,1,1';
			ksort($cfg);
			foreach($cfg as $name => $value)
				$this->dbCfg->$name = $value;
			return true;
		}
		return false;
	}
}


$ctrl = new Daemon_Scyzoryk_Controller_Config($cfg);
$ctrl->execute();
