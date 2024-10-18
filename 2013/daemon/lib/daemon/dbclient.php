<?php
//@author Krzysztof Sikorski
//abstraction for standard db queries
class Daemon_DbClient
{
	protected $dbh;


	public function __construct(PDO $dbHandle)
	{
		$this->dbh = $dbHandle;
	}


	//reads maximum length of columns of selected table
	public function getColumnMaxLength($table, $column)
	{
		$sql = 'SELECT CHARACTER_MAXIMUM_LENGTH FROM information_schema.COLUMNS
			WHERE TABLE_SCHEMA = SCHEMA() AND TABLE_NAME = :table AND COLUMN_NAME = :column';
		$params = array('table' => $table, 'column' => $column);
		return $this->selectColumn($sql, $params);
	}


	//returns internal PDO handle
	public function getDbHandle()
	{
		return $this->dbh;
	}


	//internal exception handler
	private function exceptionHandler(PDOException $e, $duplicateMsg = null)
	{
		//prepare params
		$sqlstate = $e->getCode();
		$dbMessage = $e->getMessage();
		//check error type
		if('23000' == $sqlstate && false !== stripos($dbMessage, 'duplicate'))
		{
			$message = $duplicateMsg ? $duplicateMsg : 'Wybrany obiekt już istnieje.';
			Daemon_MsgQueue::add($message);
		}
		else throw $e;
	}


	//executes a query, returns the statement resource
	public function execute($sql, array $params = array(), $duplicateMsg = null)
	{
		try
		{
			$sth = $this->dbh->prepare($sql);
			foreach((array)$params as $name => $value)
				$sth->bindValue(':'.$name, $value, self::paramType($value));
			$sth->execute();
			return $sth;
		}
		catch(PDOException $e)
		{
			$this->exceptionHandler($e, $duplicateMsg);
			return null;
		}
	}


	//returns ID of last inserted row
	public function lastInsertId()
	{
		return $this->dbh->lastInsertId();
	}


	//returns appriopriate PDO::PARAM_X constant
	public static function paramType($value)
	{
		if(is_null($value))
			return PDO::PARAM_NULL;
		elseif(is_int($value))
			return PDO::PARAM_INT;
		else return PDO::PARAM_STR;
	}


	//executes a generic non-select query
	public function query($sql, array $params = array(), $duplicateMsg = null)
	{
		$sth = $this->execute($sql, $params, $duplicateMsg);
		return !is_null($sth);
	}


	//quotes value for safe use in query
	public function quote($value)
	{
		return $this->dbh->quote($value, self::paramType($value));
	}


	//select multiple rows from table
	public function selectAll($sql, array $params = array())
	{
		$sth = $this->execute($sql, $params);
		if(is_null($sth))
			return null;
		return $sth->fetchAll(PDO::FETCH_ASSOC);
	}


	//select single column from table
	public function selectColumn($sql, array $params = array())
	{
		$sth = $this->execute($sql, $params);
		if(is_null($sth))
			return null;
		return $sth->fetchAll(PDO::FETCH_COLUMN, 0);
	}


	//select single row from table (as array)
	public function selectRow($sql, array $params = array())
	{
		$sth = $this->execute($sql, $params);
		if(is_null($sth))
			return null;
		return $sth->fetch(PDO::FETCH_ASSOC);
	}


	//select single row from table (as object)
	public function selectObject($sql, array $params = array(), $className = 'stdClass')
	{
		$sth = $this->execute($sql, $params);
		if(is_null($sth))
			return null;
		return $sth->fetchObject($className);
	}


	//select single value from table
	public function selectValue($sql, array $params = array())
	{
		$sth = $this->execute($sql, $params);
		if(is_null($sth))
			return null;
		return $sth->fetchColumn(0);
	}
}
