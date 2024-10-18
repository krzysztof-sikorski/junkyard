<?php
//@author Krzysztof Sikorski
ignore_user_abort(true);
error_reporting(E_ALL|E_STRICT);
iconv_set_encoding('internal_encoding', 'UTF-8');
mb_internal_encoding('UTF-8');
date_default_timezone_set('Europe/Warsaw');
setlocale(LC_ALL, 'pl_PL.utf8');

if (isset($_GET['phpinfo']) && $_GET['phpinfo'] == 'Waaghh!')
{
	header('Content-Type: text/html; charset=UTF-8');
	phpinfo();
	exit;
}

if(extension_loaded('zlib') && !ini_get('zlib.output_compression'))
	ob_start('ob_gzhandler'); //setting zlib.output_compression doesn't work
header('Content-Type: text/plain; charset=UTF-8');
header('X-UA-Compatible: IE=edge');

$applicationRoot = realpath(dirname(__FILE__).DIRECTORY_SEPARATOR.'..');
set_include_path($applicationRoot.DIRECTORY_SEPARATOR.'lib');
require'daemon.php';
spl_autoload_register(array('Daemon', 'autoload'));

Daemon_ErrorHandler::$logDir = $applicationRoot.DIRECTORY_SEPARATOR.'log';
set_error_handler(array('Daemon_ErrorHandler', 'errorHandler'));
set_exception_handler(array('Daemon_ErrorHandler', 'exceptionHandler'));

return new Daemon_Config($applicationRoot);
