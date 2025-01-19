<?php
//@author Krzysztof Sikorski
//container for miscelaneous functions
class Daemon
{


	//class autoloader
	public static function autoload($className)
	{
		$className = mb_strtolower(str_replace('_', DIRECTORY_SEPARATOR, $className));
		spl_autoload($className, '.php');
	}


	//creates Daemon_DbClient object using specified config
	public static function createDbClient(Daemon_Config $cfg)
	{
		$dsn = sprintf('mysql:host=%s;dbname=%s', $cfg->dbHost, $cfg->dbSchema);
		$params = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8', time_zone = '+1:00'");
		$dbh = new PDO($dsn, $cfg->dbUser, $cfg->dbPassword, $params);
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		return new Daemon_DbClient($dbh);
	}


	//prepares multiline text for displaying, also inserts some basic tags
	public static function formatMessage($txt, $markup = false)
	{
		$txt = nl2br(htmlspecialchars($txt, ENT_QUOTES));
		if($markup)
		{
			$txt = preg_replace('@\[img\](.+)\[/img\]@uU', '<img src="$1" alt="$1" class="bbcode"/>', $txt);
			$txt = preg_replace('@\[url=(.+)\](.+)\[/url\]@uU', '<a href="$1" rel="nofollow">$2</a>', $txt);
			$txt = preg_replace('@\[url\]([img]){0}(.+)\[/url\]@uU', '<a href="$1" rel="nofollow">$1</a>', $txt);
			$txt = preg_replace('@(^|>|\s)(https://\S+)($|<|\s)@muU', '$1<a href="$2" rel="nofollow">$2</a>$3', $txt);
			$txt = preg_replace('@(^|>|\s)(http://\S+)($|<|\s)@muU', '$1<a href="$2" rel="nofollow">$2</a>$3', $txt);
			$txt = preg_replace('@\[b\](.+)\[/b\]@uU', '<b>$1</b>', $txt);
			$txt = preg_replace('@\[i\](.+)\[/i\]@uU', '<i>$1</i>', $txt);
			$txt = preg_replace('@\[u\](.+)\[/u\]@uU', '<u>$1</u>', $txt);
			$txt = preg_replace('@\[s\](.+)\[/s\]@uU', '<s>$1</s>', $txt);
			$txt = preg_replace('@\[sub\](.+)\[/sub\]@uU', '<sub>$1</sub>', $txt);
			$txt = preg_replace('@\[sup\](.+)\[/sup\]@uU', '<sup>$1</sup>', $txt);
		}
		return $txt;
	}


	//returns value from array, or defaults if it doesn't exist
	public static function getArrayValue(array $a, $name, $default = null)
	{
		return isset($a[$name]) ? $a[$name] : $default;
	}


	//implodes whitespace, optionally preserving newlines
	public static function normalizeString($string, $preserveNewlines = false)
	{
		$string = str_replace(array("\r\n","\r"), "\n", $string); //unix newlines
		if($preserveNewlines)
			$string = preg_replace('/[^\S\n]+/', ' ', $string);
		else $string = preg_replace('/\s+/', ' ', $string);
		$string = trim($string);
		return $string;
	}


	//generates a password hash
	public static function passwordHash($salt, $text)
	{
		return sha1($salt . $text);
	}


	//returns random salt for password hashing
	public static function passwordSalt()
	{
		$c0 = ord('0');
		$cA = ord('a');
		$cZ = ord('z');
		$max = 10+$cZ-$cA;
		$salt = '';
		for($i = 0; $i < 8; ++$i)
		{
			$x = mt_rand(0, $max);
			if($x < 10)
				$salt .= chr($c0 + $x);
			else $salt .= chr($cA + $x - 10);
		}
		return $salt;
	}


	//redirects to selected url
	public static function redirect($url)
	{
		session_write_close(); //just in case
		header('Content-Type: text/html; charset=UTF-8');
		header(sprintf('Location: %s', $url), true, 303); //"See Other" status
		printf('<!DOCTYPE html><html lang="en"><title>Redirect</title><p><a href="%s">continue</a></p>', $url);
	}
}
