<?php
//@author Krzysztof Sikorski
class Daemon_Config
{
	public $applicationRoot;
	public $applicationTitle = 'Daemon 2';
	public $applicationUrl;
	public $applicationMail;
	public $dbHost;
	public $dbSchema;
	public $dbUser;
	public $dbPassword;
	public $dbPrefix;
	public $minifyHtml = false;
	public $sessionName = 'sessid';
	public $tsDelta = 0.5;


	public function __construct($applicationRoot = null)
	{
		$this->applicationRoot = (string) $applicationRoot;
		$hostname = mb_strtolower(getenv('SERVER_NAME'));
		if(!$hostname)
			$hostname = '_cron';
		$fileName = $hostname . '.php';
		$this->loadFile($this->getFilePath('cfg', $fileName));
	}


	//loads config from file
	public function loadFile($path)
	{
		if(is_readable($path))
		{
			$data = (array) include $path;
			foreach($data as $key=>$value)
				$this->$key = $value;
		}
	}


	//implodes parameters into relative path and prepends it with root
	public function getFilePath(/*...*/)
	{
		$aPath = array_filter(func_get_args());
		array_unshift($aPath, $this->applicationRoot);
		return implode(DIRECTORY_SEPARATOR, $aPath);
	}


	//generates URL from relative path
	public function getUrl($path)
	{
		return $path ? "$this->applicationUrl$path" : $this->applicationUrl;
	}
}
