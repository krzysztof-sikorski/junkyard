<?php
//@author Krzysztof Sikorski
class Daemon_DbObject_ItemTemplate extends Daemon_DbObject
{
	protected $_tableName = 'item_templates';
	protected $_index = array('id');
	public $id;
	public $name;
	public $pstr_p_p = 1, $pstr_p_m = 1, $pstr_c_p = 1, $pstr_c_m = 1;
	public $patk_p_p = 1, $patk_p_m = 1, $patk_c_p = 1, $patk_c_m = 1;
	public $pdef_p_p = 1, $pdef_p_m = 1, $pdef_c_p = 1, $pdef_c_m = 1;
	public $pres_p_p = 1, $pres_p_m = 1, $pres_c_p = 1, $pres_c_m = 1;
	public $mstr_p_p = 1, $mstr_p_m = 1, $mstr_c_p = 1, $mstr_c_m = 1;
	public $matk_p_p = 1, $matk_p_m = 1, $matk_c_p = 1, $matk_c_m = 1;
	public $mdef_p_p = 1, $mdef_p_m = 1, $mdef_c_p = 1, $mdef_c_m = 1;
	public $mres_p_p = 1, $mres_p_m = 1, $mres_c_p = 1, $mres_c_m = 1;
	public $armor_p = 1, $armor_m = 1;
	public $speed_p = 1, $speed_m = 1;
	public $regen_p = 1, $regen_m = 1;
}
