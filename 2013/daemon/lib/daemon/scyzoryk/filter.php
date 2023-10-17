<?php
//@author Krzysztof Sikorski
class Daemon_Scyzoryk_Filter extends stdClass
{
	const SESSION_VARNAME = 'scf';
	private $cols;
	private $data;


	public function __construct($name, array $extraCols = array(), $noSession = false)
	{
		$this->cols = array_merge(array('id', 'name'), (array) $extraCols);
		if(!isset($_SESSION[self::SESSION_VARNAME][$name]))
			$_SESSION[self::SESSION_VARNAME][$name] = array();
		if(!$noSession)
			$this->data = & $_SESSION[self::SESSION_VARNAME][$name];
		else $this->data = array();
	}


	public function __get($name)
	{
		return isset($this->data[$name]) ? $this->data[$name] : null;
	}


	public function __isset($name)
	{
		return in_array($name, $this->cols) ? true : isset($this->data[$name]);
	}


	public function __set($name, $value)
	{
		$this->data[$name] = $value;
	}


	public function __unset($name)
	{
		unset($this->data[$name]);
	}
}
