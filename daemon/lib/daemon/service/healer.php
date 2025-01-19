<?php
//@author Krzysztof Sikorski
class Daemon_Service_Healer extends Daemon_Service
{
	private $deltaHealthMin = 10;
	private $deltaHealthMax = 15;
	private $deltaManaMin = 5;
	private $deltaManaMax = 10;


	public function execute($params)
	{
		$this->setParams();
		//run commands
		$this->isCommand = $this->runCommands($params);
		//generate output
		ob_start();
		$this->view->characterData = $this->characterData;
		$this->view->bankEnabled = $this->bankEnabled;
		$this->view->deltaHealthMin = $this->deltaHealthMin;
		$this->view->deltaHealthMax = $this->deltaHealthMax;
		$this->view->deltaManaMin = $this->deltaManaMin;
		$this->view->deltaManaMax = $this->deltaManaMax;
		$this->view->display('service/healer.xml');
		$this->eventLog = ob_get_clean();
	}


	private function runCommands($params)
	{
		if(isset($params['heal']))
		{
			$this->heal($params['heal'], $this->bankEnabled);
			return true;
		}
		if(isset($params['rest']))
		{
			$this->rest();
			return true;
		}
		return false;
	}


	//heals a random amount of health and mana
	public function heal($gold, $bankEnabled)
	{
		if (!$this->characterData->payGold($gold, $bankEnabled))
			return false;
		$this->characterData->health += mt_rand($gold * $this->deltaHealthMin, $gold * $this->deltaHealthMax);
		$this->characterData->mana += mt_rand($gold * $this->deltaManaMin, $gold * $this->deltaManaMax);
		if($this->characterData->health > $this->characterData->health_max)
			$this->characterData->health = $this->characterData->health_max;
		if($this->characterData->mana > $this->characterData->mana_max)
			$this->characterData->mana = $this->characterData->mana_max;
		$this->characterData->put();
		return true;
	}


	public function setParams()
	{
		$dbCfg = new Daemon_DbConfig($this->dbClient);
		$params = $dbCfg->healer;
		if(is_scalar($params))
			$params = explode(',', $params);
		if(isset($params[0], $params[1], $params[2], $params[3]))
		{
			$this->deltaHealthMin = (int) $params[0];
			$this->deltaHealthMax = (int) $params[1];
			$this->deltaManaMin = (int) $params[2];
			$this->deltaManaMax = (int) $params[3];
		}
	}


	//rests one turn without events
	public function rest()
	{
		if(!$this->characterData->checkTurnCosts())
			return false;
		$this->characterData->regen(true);
		$this->characterData->put();
		return true;
	}
}
