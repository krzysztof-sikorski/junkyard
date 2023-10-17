<?php
//@author Krzysztof Sikorski
//wrapper for PHPTAL engine
require_once'PHPTAL.php';


class Daemon_View_Filter implements PHPTAL_Filter
{
	public function filter($str)
	{
		$str = preg_replace('/\s+/u', ' ', $str);
		return preg_replace('/> /u', ">\n", $str);
	}
}


class Daemon_View
{
	public $subtitle;
	public $subtitleDetails;
	public $titlePrefix;
	private $applicationTitle; //for setPageTitle()
	private $filter;
	private $phptal;
	const MODE_HTML = PHPTAL::HTML5;
	const MODE_ATOM = PHPTAL::XML;
	const PAGING_MAX_ITEMS = 10;


	public function __construct(Daemon_Config $cfg)
	{
		$this->applicationTitle = $cfg->applicationTitle;
		$this->phptal = new PHPTAL();
		$this->phptal->setTemplateRepository(array($cfg->getFilePath('tpl')));
		$this->phptal->setPhpCodeDestination($cfg->getFilePath('tmp'));
		$this->filter = new Daemon_View_Filter();
		if($cfg->minifyHtml)
			$this->phptal->setPostFilter($this->filter);
	}


	public function __set($name, $value)
	{
		$this->phptal->$name = $value;
	}


	//displays page content
	public function display($templateName, $outputMode = self::MODE_HTML)
	{
		if($outputMode != self::MODE_HTML)
		{
			$contentType = 'Content-Type:application/atom+xml;charset=UTF-8';
			$this->phptal->setOutputMode(PHPTAL::XML);
		}
		else
		{
			$contentType = 'Content-Type:text/html;charset=UTF-8';
			$this->phptal->setOutputMode(PHPTAL::HTML5);
		}
		$this->phptal->setTemplate($templateName);
		header($contentType);
		echo $this->phptal->execute();
	}


	//generates channel menu for chat page
	public function getChatMenu($channels, $channelId = null)
	{
		$menu = array();
		foreach($channels as $key => $row)
			$menu[] = array('name' => $row['name'], 'url' => ($key != $channelId) ? "?v=$key" : null);
		return $menu;
	}


	//generates menu for statistics pages
	public function getStatisticsMenu($type = null)
	{
		$menu = array(
			'status' => array('name' => 'Status', 'url' => 'stats'),
			'characters' => array('name' => 'Postacie', 'url' => 'stats-characters'),
			'clans' => array('name' => 'Klany', 'url' => 'stats-clans'),
			'duels' => array('name' => 'Pojedynki', 'url' => 'stats-duels'),
			'battles' => array('name' => 'Bitwy', 'url' => 'stats-battles'),
		);
		if(isset($menu[$type]))
			$menu[$type]['url'] = null;
		return $menu;
	}


	public function setGameHeader($playerId, $activeCharacter = null, $characterData = null, $location = null)
	{
		$this->__set('playerId', $playerId);
		$this->__set('activeCharacter', $activeCharacter);
		$this->__set('characterData', $characterData);
		$this->__set('location', $location);
	}


	public function setMessages($messages)
	{
		$this->__set('pageMessages', $messages);
	}


	public function setPageSkin($skinId)
	{
		$skinDirUrls = Daemon_Dictionary::$skinDirUrls;
		if (isset($skinDirUrls[$skinId]))
			$url = $skinDirUrls[$skinId];
		else
			$url = array_shift($skinDirUrls);
		$this->__set('pageSkinName', $skinId);
		$this->__set('pageSkinUrl', $url);
	}


	public function setPageTitle($subtitle = null, $details = null, $isCommand = false)
	{
		$title = $this->applicationTitle;
		if($subtitle)
		{
			if($details)
				$title = sprintf('%s: %s - %s', $subtitle, $details, $title);
			else $title = sprintf('%s - %s', $subtitle, $title);
		}
		if($isCommand)
			$title = sprintf('[cmd] %s', $title);
		$this->__set('pageTitle', $title);
	}
}
