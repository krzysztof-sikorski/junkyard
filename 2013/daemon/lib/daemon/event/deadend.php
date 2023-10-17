<?php
//@author Krzysztof Sikorski
class Daemon_Event_Deadend extends Daemon_EventInterface
{


	public function execute($params)
	{
		$this->clearEvent();
		ob_start();
		$this->view->display('event/deadend.xml');
		return ob_get_clean();
	}
}
