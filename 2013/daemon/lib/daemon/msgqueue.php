<?php
//@author Krzysztof Sikorski
// session-based message queue
class Daemon_MsgQueue
{
	const VARNAME = 'msg';


	public static function add($txt)
	{
		$_SESSION[self::VARNAME][] = (string)$txt;
	}


	public static function getAll()
	{
		if(isset($_SESSION[self::VARNAME]))
		{
			$messages = (array) $_SESSION[self::VARNAME];
			unset($_SESSION[self::VARNAME]);
			return $messages;
		}
		else return null;
	}


}
