<?php
//@author Krzysztof Sikorski
$cfg = require_once'../_init.php';


class Daemon_Scyzoryk_Controller_Page extends Daemon_Scyzoryk_Controller
{
	protected $pageSubtitle = 'Generator przedmiotów';
	protected $pageTemplatePath = 'scyzoryk/generator.xml';
	private $dbCfg;
	//generator
	private $itemWeaponTypes;
	private $itemArmorTypes;
	private $itemTemplates;
	//mieszator
	private $weightA;
	private $weightB;


	protected function prepareModel()
	{
		//generator
		$this->dbCfg = new Daemon_DbConfig($this->dbClient);
		$this->itemWeaponTypes = Daemon_Dictionary::$itemWeaponTypes;
		unset($this->itemWeaponTypes['']);
		$this->itemArmorTypes = Daemon_Dictionary::$itemArmorTypes;
		unset($this->itemArmorTypes['']);
		$this->itemTemplates = $this->browser->getItemTemplates();
	}


	protected function prepareView()
	{
		$this->view->itemWeaponTypes = $this->itemWeaponTypes;
		$this->view->itemArmorTypes = $this->itemArmorTypes;
		$this->view->itemTemplates = $this->itemTemplates;
	}


	protected function runCommands()
	{
		//generate random item
		if (isset($_POST['generate'], $_POST['id'], $_POST['name'],
			$_POST['type'], $_POST['value'], $_POST['template']))
		{
			if (empty($_POST['id']))
			{
				Daemon_MsgQueue::add('Musisz wybrać ID.');
				return true;
			}
			if (empty($_POST['template']))
			{
				Daemon_MsgQueue::add('Musisz wybrać szablon przedmiotu.');
				return true;
			}
			$item = $this->generateItem($_POST['id'], $_POST['name'],
				$_POST['type'], $_POST['value'], $_POST['template']);
			if ($item && ($item->item_id))
			{
				$item->attachDbClient($this->dbClient);
				$item->put();
				$url = $this->cfg->getUrl('scyzoryk/item-edit?id='.urlencode($item->item_id));
				Daemon::redirect($url);
				exit;
			}
			return true;
		}
		//merge items
		if (isset($_POST['merge'], $_POST['id'], $_POST['name']))
		{
			//multipliers
			$this->weightA = isset($_POST['weightA']) ? max(1, (int) $_POST['weightA']) : 1;
			$this->weightB = isset($_POST['weightB']) ? max(1, (int) $_POST['weightB']) : 1;
			//base items
			$baseItemA = new Daemon_DbObject_Item();
			$baseItemA->attachDbClient($this->dbClient);
			if(isset($_POST['baseA']))
				$baseItemA->get(array('item_id' => $_POST['baseA']));
			$baseItemB = new Daemon_DbObject_Item();
			$baseItemB->attachDbClient($this->dbClient);
			if(isset($_POST['baseB']))
				$baseItemB->get(array('item_id' => $_POST['baseB']));
			//result item
			$resultItem = new Daemon_DbObject_Item();
			$resultItem->attachDbClient($this->dbClient);
			if($this->editId)
				$resultItem->get(array('item_id' => $this->editId));
			$resultItem->item_id = $_POST['id'];
			$resultItem->name = $_POST['name'];
			$resultItem->type = $baseItemA->type;
			$resultItem->damage_type = $baseItemA->damage_type;
			$resultItem->special_type = $baseItemA->special_type;
			$resultItem->regen = ($baseItemA->regen + $baseItemB->regen) / 2;
			//merge values
			$keys = array('value', 'special_param',
				'pstr_p', 'pstr_c', 'patk_p', 'patk_c',
				'pdef_p', 'pdef_c', 'pres_p', 'pres_c',
				'mstr_p', 'mstr_c', 'matk_p', 'matk_c',
				'mdef_p', 'mdef_c', 'mres_p', 'mres_c',
				'armor', 'speed', 'regen');
			foreach ($keys as $k)
				$resultItem->$k = $this->mergeValues($baseItemA->$k, $baseItemB->$k);
			//save item
			if ($resultItem->item_id)
			{
				$resultItem->validate();
				$resultItem->updateSuggestedValue($this->dbCfg);
				$resultItem->put();
				Daemon::redirect($this->cfg->getUrl('scyzoryk/item-edit?id='.urlencode($resultItem->item_id)));
				exit;
			}
			return true;
		}
	}


	private function mergeValues($valueA, $valueB)
	{
		$x = $this->weightA * (int) $valueA + $this->weightB * (int) $valueB;
		$y = max(1, $this->weightA + $this->weightB);
		return round($x / $y);
	}


	private function generateItem($id, $name, $type, $value, $templateId)
	{
		//initialise
		$item = new Daemon_DbObject_Item();
		$item->item_id = $id;
		$item->name = $name;
		$isWeapon = false;
		$isArmor = false;
		if (isset($this->itemWeaponTypes[$type]))
		{
			$isWeapon = true;
			$item->type = $_POST['type'];
			$item->damage_type = 'p';
		}
		elseif (isset($this->itemArmorTypes[$type]))
		{
			$isArmor = true;
			$item->type = $_POST['type'];
		}
		if (!$isWeapon && !$isArmor)
		{
			Daemon_MsgQueue::add('Wybierz typ przedmiotu.');
			return null;
		}
		//read chances
		$chances = array();
		$template = new Daemon_DbObject_ItemTemplate();
		$template->attachDbClient($this->dbClient);
		$template->get(array('id' => $templateId));
		foreach (get_object_vars($template) as $key => $val)
		{
			if (($key[0] != '_') && !empty($val) && is_numeric($val))
				$chances[$key] = (float) $val;
		}
		//generate stats
		$specialKeys = array('armor', 'speed', 'regen');
		while ($item->suggested_value < $value)
		{
			$chanceKey = $this->getRandomKey($chances);
			unset($matches);
			preg_match('/^(.+)_([^_]+)$/', $chanceKey, $matches);
			if (isset($matches[0], $matches[1]))
			{
				$key = $matches[1];
				$sign = ($matches[2] != 'm') ? +1 : -1;
				$item->$key += $sign;
			}
			else
			{
				Daemon_MsgQueue::add('Ten szablon nie nadaje się do generowania - suma szans nie jest dodatnia.');
				return null;
			}
			$item->updateSuggestedValue($this->dbCfg);
		}
		//set price
		$item->value = round($item->suggested_value);
		return $item;
	}


	private function getRandomKey(array $chances)
	{
		$chanceSum = array_sum($chances);
		if ($chanceSum < 1)
			return null;
		$key = null;
		$d256 = mt_rand(0, 255);
		foreach ($chances as $key => $val)
		{
			$chance = 256 * $val / $chanceSum;
			if($d256 < $chance)
				break;
			$d256 -= $chance;
		}
		return $key;
	}
}


$ctrl = new Daemon_Scyzoryk_Controller_Page($cfg);
$ctrl->execute();
