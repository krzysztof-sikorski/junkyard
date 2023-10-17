<?php
//@author Krzysztof Sikorski
class Daemon_Dictionary
{
	public static $bossStatuses = array('hidden' => 'ukryty', 'active' => 'aktywny', 'defeated' => 'pokonany');

	public static $characterAttributes = array(
		'str' => 'Siła', 'dex' => 'Zręczność', 'vit' => 'Wytrzymałość', 'pwr' => 'Moc', 'wil' => 'Siła Woli');

	public static $characterSkills = array(
		'pstr' => 'Silny Cios', 'patk' => 'Przycelowanie',
		'pdef' => 'Unik', 'pres' => 'Twardziel', 'preg' => 'Regeneracja',
		'mstr' => 'Koncentracja', 'matk' => 'Magia Bojowa',
		'mdef' => 'Kontrzaklęcie', 'mres' => 'Antymagia', 'mreg' => 'Medytacja');

	public static $combatAttackTypes = array('p' => 'fizyczny', 'm' => 'magiczny');

	public static $combatAttackSpecials = array(
		Daemon_DbObject_CombatUnit::SP_POISON => 'trucizna',
		Daemon_DbObject_CombatUnit::SP_VAMPIRE => 'wampiryzm',
		Daemon_DbObject_CombatUnit::SP_ETHER => 'eteryczny',
		Daemon_DbObject_CombatUnit::SP_BLOODY => 'krwawy',
		Daemon_DbObject_CombatUnit::SP_STUN => 'ogłuszenie',
		Daemon_DbObject_CombatUnit::SP_FACTION => 'nienawiść',
		Daemon_DbObject_CombatUnit::SP_SWARM => 'stado',
	);

	public static $combatArmorSpecials = array(
		Daemon_DbObject_CombatUnit::SP_DEMON => 'demon',
		Daemon_DbObject_CombatUnit::SP_ANTIPOISON => 'odporność na trucizny',
		Daemon_DbObject_CombatUnit::SP_ANTIVAMP => 'odporność na wampiryzm',
		Daemon_DbObject_CombatUnit::SP_SHOCK => 'porażenie',
		Daemon_DbObject_CombatUnit::SP_FACTION => 'fanatyzm',
	);

	public static $equipmentButtons = array('use' => 'użyj', 'equip' => 'załóż', 'unequip' => 'zdejmij');

	public static $equipmentFlags = array('bound' => 'przypisany', 'identified' => 'zidentyfikowany');

	public static $equipmentGroups = array(
		'weapon1h' => 'BROŃ 1R i TARCZE', 'weapon2h' => 'BROŃ 2R',
		'armor' => 'PANCERZE', 'helmet' => 'HEŁMY', 'gloves' => 'RĘKAWICE',
		'boots' => 'BUTY', 'pendant' => 'NASZYJNIKI', 'accesory' => 'DODATKI',
		'item' => 'INNE PRZEDMIOTY');

	public static $equipmentSlots = array(
		'hand_a' => 'główna ręka', 'hand_b' => 'druga ręka',
		'armor' => 'pancerz', 'helmet' => 'hełm', 'gloves' => 'rękawice', 'boots' => 'buty',
		'pendant' => 'naszyjnik', 'accesory_a' => 'dodatek A', 'accesory_b' => 'dodatek B');

	public static $genders = array('f' => 'kobieta', 'm' => 'mężczyzna', 'n' => 'nieokreślona');

	public static $generatorItemTypes = array(
		'weapon1h' => 'broń 1R', 'weapon2h' => 'broń 2R',
		'armor' => 'pancerz', 'helmet' => 'hełm', 'gloves' => 'rękawice',
		'boots' => 'buty', 'pendant' => 'naszyjnik', 'accesory' => 'dodatek');

	public static $itemTypes = array(
		'weapon1h' => 'broń 1R', 'weapon2h' =>'broń 2R',
		'armor' => 'pancerz', 'helmet' => 'hełm', 'gloves' => 'rękawice',
		'boots' => 'buty', 'pendant' => 'naszyjnik', 'accesory' => 'dodatek',
		'item' => 'niezakładalny');

	public static $itemDamageTypes = array('' => 'brak', 'p' => 'fizyczne', 'm' => 'magiczne');

	public static $itemWeaponTypes = array('weapon1h' => 'jednoręczna', 'weapon2h' =>'dwuręczna');

	public static $itemArmorTypes = array(
		'armor' => 'pancerz', 'helmet' => 'hełm', 'gloves' => 'rękawice',
		'boots' => 'buty', 'pendant' => 'naszyjnik', 'accesory' => 'dodatek');

	public static $locationTypes = array('normal' => 'zwykła', 'arena'=>'arena', 'caern'=>'caern', 'boss' => 'boss');

	public static $missionProgress = array(
		'active' => 'aktywna', 'completed' => 'ukończona', 'rewarded' => 'nagrodzona');

	public static $monsterClasses = array(1 => 'słaby', 2 => 'średni', 3 => 'silny', 4 => 'epicki');

	public static $monsterClassLevels = array(1 => 0, 2 => 20, 3 => 45, 4 => 70);

	public static $serviceTypes = array(
		'bank'=>'bank', 'healer' => 'uzdrowiciel', 'shop' => 'sklep', 'temple'=>'świątynia');

	public static $skinDirUrls = array('Ciemny' => 'static/dark', 'Jasny' => 'static/light');
}
