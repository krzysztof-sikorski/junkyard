<?php
//@author Krzysztof Sikorski
$cfg = require_once'../_init.php';


class Daemon_Scyzoryk_Controller_Config extends Daemon_Scyzoryk_Controller
{
	protected $pageSubtitle = 'Ustawienia generatora';
	protected $pageTemplatePath = 'scyzoryk/config-generator.xml';
	private $dbCfg;
	private $itemTypes;


	protected function prepareModel()
	{
		$this->dbCfg = new Daemon_DbConfig($this->dbClient);
		$this->itemTypes = Daemon_Dictionary::$generatorItemTypes;
	}


	protected function prepareView()
	{
		$this->view->generatorBaseValue = $this->dbCfg->generatorBaseValue;
		$generatorOptions = array();
		foreach ($this->itemTypes as $type => $name)
		{
			$generatorOptions[$type] = array(
				'name' => $name,
				'weights' => $this->dbCfg->getGeneratorWeights("$type")
			);
		}
		$this->view->generatorOptions = $generatorOptions;
	}


	protected function runCommands()
	{
		if(isset($_POST['baseValue'], $_POST['weights']) && is_array($_POST['weights']))
		{
			$this->dbCfg->generatorBaseValue = max(1, (int) $_POST['baseValue']);
			foreach (array_keys($this->itemTypes) as $type)
				$this->dbCfg->setGeneratorWeights($type, $_POST['weights'][$type]);
			return true;
		}
		return false;
	}
}


$ctrl = new Daemon_Scyzoryk_Controller_Config($cfg);
$ctrl->execute();
