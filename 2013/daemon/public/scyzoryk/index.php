<?php
//@author Krzysztof Sikorski
$cfg = require_once'../_init.php';


class Daemon_Scyzoryk_Controller_Index extends Daemon_Scyzoryk_Controller
{
	protected $pageSubtitle = 'Scyzoryk';
	protected $pageTemplatePath = 'scyzoryk/index.xml';


	protected function prepareView()
	{
		//Ile kasy za przedmiot (od poziomu potwora)
		$prices = array();
		$cecha = 0;
		$cena = 3;
		$i = 1;
		$p2 = sqrt(2);
		while ($i<90)
		{
			$i *= 1.22;
			$cena = round($cena * $p2);
			$prices[round($i)] = $cena;
		}
		$this->view->prices = $prices;

		//Ile bonusu powinien mieć przedmiot za potwora
		$bonuses = array();
		$cecha = 0;
		$cena = 0;
		for ($i = 1; $i < 100; ++$i)
		{
			if ($i < 11)
				$mnoz=3;
			elseif ($cecha<31)
				$mnoz=4;
			else
				$mnoz=5;
			$cena = round($cena * 0.9 + $i * $mnoz);
			$cecha += $mnoz;
			$st = round($cecha * $i / (100+$i));
			$bonuses[] = array('bonusp' => $i, 'bonusc' => $st, 'price' => $cena);
		}
		$this->view->bonuses = $bonuses;
	}
}


$ctrl = new Daemon_Scyzoryk_Controller_Index($cfg);
$ctrl->execute();
