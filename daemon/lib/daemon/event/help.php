<?php
//@author Krzysztof Sikorski
class Daemon_Event_Help extends Daemon_EventInterface
{


	public function execute($params)
	{
		$this->clearEvent();
		return '<b>Aaarghhh!</b>';
	}
}
