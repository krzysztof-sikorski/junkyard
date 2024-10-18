<?php
//@author Krzysztof Sikorski
$cfg = require_once'../_init.php';


class Daemon_Scyzoryk_Controller_ItemEdit extends Daemon_Scyzoryk_Controller
{
	protected $pageSubtitle = 'Przedmioty';
	protected $pageTemplatePath = 'scyzoryk/item-edit.xml';
	private $dbCfg;
	private $item;
	private $itemProperties;


	protected function prepareModel()
	{
		$this->dbCfg = new Daemon_DbConfig($this->dbClient);
		$this->item = new Daemon_DbObject_Item();
		$this->item->attachDbClient($this->dbClient);
		if($this->editId)
			$this->item->get(array('item_id' => $this->editId));
	}


	protected function prepareView()
	{
		$this->pageSubtitleDetails = $this->item ? $this->item->name : null;
		$this->view->item = $this->item;
		$this->view->itemTypes = Daemon_Dictionary::$itemTypes;
		$this->view->itemWeaponTypes = Daemon_Dictionary::$itemWeaponTypes;
		$this->view->itemDamageTypes = Daemon_Dictionary::$itemDamageTypes;
		$this->view->itemArmorTypes = Daemon_Dictionary::$itemArmorTypes;
		$this->view->combatAttackSpecials = Daemon_Dictionary::$combatAttackSpecials;
		$this->view->combatArmorSpecials = Daemon_Dictionary::$combatArmorSpecials;
		$itemUsableSpecials = array();
		$sql = "SELECT special_id, name FROM item_specials ORDER BY name";
		foreach($this->dbClient->selectAll($sql) as $row)
			$itemUsableSpecials[$row['special_id']] = $row['name'];
		$this->view->itemSpecials = $itemUsableSpecials;
	}


	protected function runCommands()
	{
		if(is_null($this->item))
			return false;
		if(isset($_POST['id'], $_POST['name'], $_POST['p']) && $_POST['id'])
		{
			$this->item->item_id = $_POST['id'];
			$this->item->name = $_POST['name'];
			$this->item->type = Daemon::getArrayValue($_POST, 'type');
			$this->item->value = Daemon::getArrayValue($_POST, 'value');
			$this->item->description = Daemon::getArrayValue($_POST, 'description');
			$this->item->damage_type = Daemon::getArrayValue($_POST, 'damage_type');
			$this->item->special_type = Daemon::getArrayValue($_POST, 'special_type');
			$this->item->special_param = Daemon::getArrayValue($_POST, 'special_param');
			$keys = array_keys(get_class_vars('Daemon_DbObject_Item'));
			foreach($_POST['p'] as $key => $val)
				if(in_array($key, $keys))
					$this->item->$key = $val;
			$this->item->validate();
			$this->item->updateSuggestedValue($this->dbCfg);
			$this->item->put();
			return true;
		}
		return false;
	}
}


$ctrl = new Daemon_Scyzoryk_Controller_ItemEdit($cfg);
$ctrl->execute();
