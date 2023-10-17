<?php
//@author Krzysztof Sikorski
require'daemon/scyzoryk/dbrow.php';
class Daemon_Scyzoryk
{
	protected $dbClient;


	public function __construct(Daemon_DbClient $dbClient)
	{
		$this->dbClient = $dbClient;
	}


	public function deleteRows($tableName, $indexCol, array $ids)
	{
		$sql = "DELETE FROM $tableName WHERE $indexCol=:id";
		foreach($ids as $id)
			$this->dbClient->query($sql, array('id' => $id));
	}


	public function selectRow($className, $id, $id2 = null)
	{
		$tableName = constant("$className::TABLE_NAME");
		$indexCol = constant("$className::INDEX_COL");
		$indexCol2 = constant("$className::INDEX_COL2");
		if(!$tableName || !$indexCol)
			throw new InvalidArgumentException('Unsupported class name!');
		$cond = array("$indexCol=:id");
		$params = array('id' => $id);
		if($indexCol2 && $id2)
		{
			$cond[] = "$indexCol2=:id2";
			$params['id2'] = $id2;
		}
		$cond = implode(' AND ', $cond);
		$sql = "SELECT * FROM $tableName WHERE $cond";
		return $this->dbClient->selectObject($sql, $params, $className);
	}


	public function updateRow(Daemon_Scyzoryk_DbRow $row)
	{
		$row->validate();
		$className = get_class($row);
		$tableName = constant("$className::TABLE_NAME");
		$indexCol = constant("$className::INDEX_COL");
		$indexCol2 = constant("$className::INDEX_COL2");
		if(!$tableName || !$indexCol)
			throw new InvalidArgumentException('This table must be edited manually!');
		$cols = array();
		$vals = array();
		$mods = array();
		$params = array();
		$ignore = array($indexCol, $indexCol2);
		foreach($row as $col => $val)
		{
			$cols[] = $col;
			$vals[] = ":$col";
			if(!in_array($col, $ignore))
				$mods[] = "$col=:$col";
			$params[$col] = $val;
		}
		$cols = implode(', ', $cols);
		$vals = implode(', ', $vals);
		$mods = implode(', ', $mods);
		if($mods)
			$sql = "INSERT INTO $tableName ($cols) VALUES ($vals) ON DUPLICATE KEY UPDATE $mods";
		else $sql = "REPLACE INTO $tableName ($cols) VALUES ($vals)";
		$this->dbClient->query($sql, $params);
	}
}
