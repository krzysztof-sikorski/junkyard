<?php
//@author Krzysztof Sikorski
$cfg = require_once'../_init.php';


class Daemon_Scyzoryk_Controller_Page extends Daemon_Scyzoryk_Controller
{
	protected $pageSubtitle = 'Arena';
	protected $pageTemplatePath = 'scyzoryk/arena.xml';
	private $selectMode;
	private $combatCount;
	private $unitIdA;
	private $unitIdB;
	private $winsA;
	private $winsB;
	private $draws;
	private $doubleKOs;
	private $healthSumA;
	private $healthSumB;
	private $healthMinA;
	private $healthMinB;
	private $healthMaxA;
	private $healthMaxB;


	public function prepareModel()
	{
		$this->selectMode = isset($_POST['mode']) ? $_POST['mode'] : null;
		$this->combatCount = isset($_POST['n']) ? min(500, max(100, (int) $_POST['n'])) : 100;
		$this->unitIdA = isset($_POST['unitA']) ? $_POST['unitA'] : null;
		$this->unitIdB = isset($_POST['unitB']) ? $_POST['unitB'] : null;
	}


	public function prepareView()
	{
		$this->view->selectMode = $this->selectMode;
		$this->view->combatCount = $this->combatCount;
		$this->view->unitIdA = $this->unitIdA;
		$this->view->unitIdB = $this->unitIdB;
		$this->view->winsA = round(100 * $this->winsA / $this->combatCount, 2);
		$this->view->winsB = round(100 * $this->winsB / $this->combatCount, 2);
		$this->view->draws = round(100 * $this->draws / $this->combatCount, 2);
		$this->view->doubleKOs = round(100 * $this->doubleKOs / $this->combatCount, 2);
		$this->view->healthSumA = round(100 * $this->healthSumA / $this->combatCount, 2);
		$this->view->healthSumB = round(100 * $this->healthSumB / $this->combatCount, 2);
		$this->view->healthMinA = round(100 * $this->healthMinA, 2);
		$this->view->healthMinB = round(100 * $this->healthMinB, 2);
		$this->view->healthMaxA = round(100 * $this->healthMaxA, 2);
		$this->view->healthMaxB = round(100 * $this->healthMaxB, 2);

		$filter = new Daemon_Scyzoryk_Filter('combat-units');
		$filter->noChars = false;
		$unitsC = $unitsM = array();
		foreach ($this->browser->getCombatUnits($filter) as $row)
		{
			if ($row['_character'])
				$unitsC[] = $row;
			else
				$unitsM[] = $row;
		}
		$this->view->unitsC = $unitsC;
		$this->view->unitsM = $unitsM;
	}


	public function runCommands()
	{
		if(isset($_POST['attack']))
		{
			if(!$this->unitIdA || !$this->unitIdB)
			{
				Daemon_MsgQueue::add('Wybierz obie jednostki.');
				return true;
			}
			$this->winsA = 0;
			$this->winsB = 0;
			$this->draws = 0;
			$this->doubleKOs = 0;
			$this->healthSumA = 0;
			$this->healthSumB = 0;
			$unitA = new Daemon_Combat_Unit();
			$unitA->attachDbClient($this->dbClient);
			$unitA->get(array('combat_unit_id' => $this->unitIdA));
			$unitB = new Daemon_Combat_Unit();
			$unitB->attachDbClient($this->dbClient);
			$unitB->get(array('combat_unit_id' => $this->unitIdB));
			$this->healthMinA = 1.0;
			$this->healthMinB = 1.0;
			$this->healthMaxA = 0.0;
			$this->healthMaxB = 0.0;
			for ($i = 0; $i < $this->combatCount; ++$i)
			{
				$unitA->health = $unitA->health_max;
				$unitB->health = $unitB->health_max;
				$combat = new Daemon_Combat();
				$combat->addUnit('a', $unitA, true);
				$combat->addUnit('b', $unitB, false);
				$combat->execute(true);
				$deathA = ($unitA->health < 1);
				$deathB = ($unitB->health < 1);
				if ($deathA && $deathB)
					$this->doubleKOs += 1;
				elseif ($deathA)
					$this->winsB += 1;
				elseif ($deathB)
					$this->winsA += 1;
				else
					$this->draws += 1;
				$relHealthA = max(0.0, $unitA->health / $unitA->health_max);
				$relHealthB = max(0.0, $unitB->health / $unitB->health_max);
				$this->healthSumA += $relHealthA;
				$this->healthSumB += max(0.0, $relHealthB);
				$this->healthMinA = min($this->healthMinA, $relHealthA);
				$this->healthMinB = min($this->healthMinB, $relHealthB);
				$this->healthMaxA = max($this->healthMaxA, $relHealthA);
				$this->healthMaxB = max($this->healthMaxB, $relHealthB);
			}
			return true;
		}
	}
}


$ctrl = new Daemon_Scyzoryk_Controller_Page($cfg);
$ctrl->execute();
