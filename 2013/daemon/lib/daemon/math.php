<?php
//@author Krzysztof Sikorski
class Daemon_Math
{
	//calculates regen value from skill and armor bonus
	public static function combatRegen($attribute, $skill)
	{
		if($sum = $attribute + $skill)
			return round($attribute * $skill / $sum, 3);
		else return 0.0;
	}


	//calculates a single stat
	public static function combatStat($attribute, $bonus_p, $bonus_c, $skill)
	{
		return max(0, (int) round($attribute * (1 + $bonus_p/100) + $bonus_c + $skill));
	}


	//calculates power multiplier for faction boss
	public static function factionPowerMult($bossFactionId, array $factionPowers)
	{
		$sum = array_sum($factionPowers);
		if(empty($sum) || !isset($factionPowers[$bossFactionId]))
			return 1;
		return 1 + $factionPowers[$bossFactionId] / $sum;
	}


	//calculates mana regen value from skill
	public static function manaRegen($attribute, $skill)
	{
		if($sum = $attribute + $skill)
			return max(1, (int) round($attribute * $skill / $sum));
		else return 1;
	}


	//calculates spell cost
	public static function spellCost($level, $maxCost, $minCost, $delta)
	{
		$level = max(1, $level);
		return max($minCost, $maxCost - $level * $delta);
	}


	//randomly rounds a number
	public static function round($float)
	{
		$result = floor($float);
		$prob = $float - $result;
		if(mt_rand(0,255) < 256 * $prob)
			++$result;
		return (int) $result;
	}
}
