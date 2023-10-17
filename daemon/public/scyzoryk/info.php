<?php
//@author Krzysztof Sikorski
date_default_timezone_set('Europe/Warsaw');
header('Content-Type: text/html; charset=UTF-8',true);
header(sprintf('Last-Modified: %s',gmdate(DATE_RFC1123)),true);
header('Cache-Control: no-store, no-cache, must-revalidate',true);
if(!$_GET)
{
	phpinfo();
	exit;
}
if(isset($_GET['locale']))
{
	$loc = setlocale(LC_ALL, 0);
	var_dump($loc);
	$loc = setlocale(LC_ALL, 'pl_PL.utf8');
	var_dump($loc);
	exit;
}
