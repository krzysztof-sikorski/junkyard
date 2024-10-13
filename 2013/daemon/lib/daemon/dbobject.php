<?php
//@author Krzysztof Sikorski
//active record pattern
class Daemon_DbObject
{
	protected $_dbClient;
	protected $_tableName;
	protected $_index = array();//names of index columns


	public function __construct()
	{
		if($params = func_get_args())
			$this->import($params);
	}


	public function attachDbClient(Daemon_DbClient $dbClient = null)
	{
		$this->_dbClient = $dbClient;
	}


	//deletes object data from database
	public function delete()
	{
		$cond = array();
		$params = array();
		foreach($this->_index as $col)
		{
			$cond[] = "$col=:$col";
			$params[$col] = $this->$col;
		}
		$cond = implode(' AND ', $cond);
		if(!$cond)
			throw new RuntimeException('Index not specified.');
		$sql = "DELETE FROM $this->_tableName WHERE $cond";
		$this->_dbClient->query($sql, $params);
	}


	//retrieves object data from database
	public function get(array $params, $ignoreDuplicates = false)
	{
		if(!$params)
			throw new RuntimeException('Params not specified.');
		$cond = array();
		foreach(array_keys($params) as $key)
			$cond[] = "$key=:$key";
		$cond = implode(' AND ', $cond);
		$sql = "SELECT * FROM $this->_tableName WHERE $cond ORDER BY RAND() LIMIT 2";
		$data = $this->_dbClient->selectAll($sql, $params);
		if(is_array($data) && isset($data[0]))
		{
			if(!$ignoreDuplicates && (count($data) > 1))
				throw new RuntimeException('Multiple rows found.');
			foreach($data[0] as $key => $val)
				$this->$key = $val;
		}
		return true;
	}


	//copies params into object data
	public function import($params)
	{
		$keys = array_keys(get_object_vars($this));
		foreach($keys as $key)
		{
			if(isset($params[$key]) && ($key[0] != '_'))
				$this->$key = $params[$key];
		}
		$this->validate();
	}


	//stores object data in the database
	public function put()
	{
		$this->validate();
		$cols = array();
		$vals = array();
		$mods = array();
		$params = array();
		foreach($this as $col => $val)
		{
			if($col[0] != '_')
			{
				$cols[] = $col;
				$vals[] = ":$col";
				if(!in_array($col, $this->_index))
					$mods[] = "$col=:$col";
				$params[$col] = $val;
			}
		}
		$cols = implode(', ', $cols);
		$vals = implode(', ', $vals);
		$mods = implode(', ', $mods);
		if($mods)
			$sql = "INSERT INTO $this->_tableName ($cols) VALUES ($vals) ON DUPLICATE KEY UPDATE $mods";
		else $sql = "REPLACE INTO $this->_tableName ($cols) VALUES ($vals)";
		$this->_dbClient->query($sql, $params);
	}


	//checks object data
	public function validate() {}
}
