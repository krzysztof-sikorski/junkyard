<?php
//@author Krzysztof Sikorski
//manager for game world variables
class Daemon_DbConfig
{
	private $dbClient;
	private $data;


	public function __construct(Daemon_DbClient $dbClient)
	{
		$this->dbClient = $dbClient;
	}


	public function __get($name)
	{
		return $this->get($name);
	}


	public function __set($name, $value)
	{
		return $this->set($name, $value);
	}


	public function get($name)
	{
		if(!isset($this->data[$name]))
		{
			$sql = "SELECT value FROM parameters WHERE name=:name";
			$params = array('name' => $name);
			$this->data[$name] = $this->dbClient->selectValue($sql, $params);
		}
		return $this->data[$name];
	}


	public function set($name, $value)
	{
		if (empty($value))
			$value = '';
		$sql = "INSERT INTO parameters(name, value) VALUES (:name, :value) ON DUPLICATE KEY UPDATE value=:value";
		$params = array('name' => $name, 'value' => $value);
		$this->dbClient->query($sql, $params);
		$this->data[$name] = $value;
	}


	public function getGeneratorWeights($type)
	{
		$keys = array(
			'pstr_p', 'pstr_c', 'patk_p', 'patk_c', 'pdef_p', 'pdef_c', 'pres_p', 'pres_c',
			'mstr_p', 'mstr_c', 'matk_p', 'matk_c', 'mdef_p', 'mdef_c', 'mres_p', 'mres_c',
			'armor', 'speed', 'regen', 'special_param');
		return $this->getGeneratorOptions("w_$type", $keys);
	}


	private function getGeneratorOptions($key, array $keys)
	{
		$result = json_decode($this->get("generator_$key"), true);
		if (!is_array($result))
			$result = array();
		foreach ($keys as $key)
		{
			if (empty($result[$key]))
				$result[$key] = 0;
		}
		return $result;
	}


	public function setGeneratorWeights($type, array $options)
	{
		$this->setGeneratorOptions("w_$type", $options);
	}


	private function setGeneratorOptions($key, array $options)
	{
		foreach ($options as &$val)
			$val = floatval(str_replace(',', '.', $val));
		$this->set("generator_$key", json_encode($options));
	}
}
