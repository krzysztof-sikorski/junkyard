<?php
//@author Krzysztof Sikorski
class Daemon_Combat_Log
{
	private $buffer;
	public $groupCombat;


	public function __construct()
	{
		$this->clear();
	}


	public function __toString()
	{
		return $this->buffer;
	}


	public function add($text)
	{
		$this->buffer .= "$text\n";
	}


	public function clear()
	{
		$this->buffer = '';
	}


	public static function escape($str)
	{
		return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
	}


	public function txtAttackHit($name, $dmg, $magical, $critical, $poison, $shockDmg, $stun, $vampRegen)
	{
		$txt = $critical ? 'Trafienie krytyczne! ' : '';
		$txt .= sprintf('<i>%s</i> zadaje <b>%.2f</b> obrażeń %s', self::escape($name),
			$dmg, $magical ? 'magicznych' : 'fizycznych');
		if($poison)
			$txt .= ', zatruwając cel';
		if($stun)
			$txt .= ', ogłuszając cel';
		if($vampRegen)
			$txt .= sprintf(', regenerując <b>%.2f</b> HP', $vampRegen);
		if($shockDmg)
			$txt .= sprintf('. Otrzymuje <b>%.2f</b> obrażeń od porażenia', $shockDmg);
		$txt .= '.<br>';
		$this->add($txt);
	}


	public function txtAttackMiss($name, $magical)
	{
		if($magical)
			$this->add('Cel odbił zaklęcie.<br>');
		else $this->add(sprintf('<i>%s</i> chybił.<br>', self::escape($name)));
	}


	public function txtDeath($name, $flawlessName = null)
	{
		$txt = sprintf('<i>%s</i> umiera.<br>', $name);
		if($flawlessName)
		{
			$atxt = array('<i>%s</i> ziewa.', '<i>%s</i> śmieje się szyderczo.');
			$txt .= sprintf($atxt[array_rand($atxt)], self::escape($flawlessName)).'<br>';
		}
		$this->add($txt);
	}


	public function txtDemon($regen)
	{
		$this->add(sprintf('Demon w zbroi wchłonął zaklęcie. Cel regeneruje %.2f obrażeń.<br>', $regen));
	}


	public function txtPoison($name, $dmg)
	{
		$this->add(sprintf('<i>%s</i> otrzymuje %.2f obrażeń od trucizny.<br>', self::escape($name), $dmg));
	}


	public function txtRegen($name, $regen)
	{
		$this->add(sprintf('<i>%s</i> regeneruje %.2f obrażeń.<br>', self::escape($name), $regen));
	}


	public function txtRoundFooter()
	{
		$this->add('</p>');
	}


	public function txtRoundHeader($round)
	{
		$this->add(sprintf('<h3>Akcja %d</h3><p>', $round));
	}


	public function txtTargetHeader($actorName, $targetName)
	{
		if($targetName)
		{
			if ($this->groupCombat)
				$this->add(sprintf('<i>%s</i> wybiera cel: <i>%s</i>.<br>',
					self::escape($actorName), self::escape($targetName)));
		}
		else $this->add(sprintf('<i>%s</i> nie ma już przeciwników.<br>', self::escape($actorName)));
	}
}
