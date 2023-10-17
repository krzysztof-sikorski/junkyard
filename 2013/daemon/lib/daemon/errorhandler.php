<?php
//@author Krzysztof Sikorski
class Daemon_ErrorHandler
{
	public static $logDir;


	public static function errorHandler($errno, $errstr, $errfile, $errline)
	{
		$data = "LEVEL: $errno\nMESSAGE: $errstr\nFILE: $errfile\nLINE: $errline";
		$path = sprintf('%s/error.%s.log', self::$logDir, date('Ymd.His'));
		self::logError($path, $data);
	}


	public static function exceptionHandler(Exception $ex)
	{
		$data = sprintf("MESSAGE: %s\nCODE: %s\nFILE: %s\nLINE: %s\nBACKTRACE:\n%s",
			$ex->getMessage(), $ex->getCode(), $ex->getFile(), $ex->getLine(), $ex->getTraceAsString());
		$path = sprintf('%s/exception.%s.log', self::$logDir, date('Ymd.His'));
		self::logError($path, $data);
	}


	public static function logError($path, $data)
	{
		$stored = @file_put_contents($path, $data);
		if(!headers_sent())
			header('Content-Type:text/html;charset=UTF-8');
		echo'<!DOCTYPE html><html><meta charset="UTF-8"><title>500 Internal Server Error</title>';
		echo'<h1>Wystąpił błąd</h1>';
		echo'<p>Kod gry zrobił <q>Aaarghhh!</q> i przestał działać. Albo zdechła baza danych. Albo cokolwiek.</p>';
		if($stored)
		{
			echo'<p>Na szczęście nie zawiodło automatyczne logowanie błędów, więc nie musisz nic robić poza szturchnięciem admina że jego badziew znow nie działa ;)</p>';
		}
		else
		{
			echo'<p>Na dokładkę zawiodło też automatyczne logowanie błędów, więc to do Ciebie należy ciężkie zadanie powiadomienia o nim admina. Pamiętaj żeby podać wszystkie możliwe informacje o błędzie, ułatwi to lub w ogóle umożliwi jego wytropienie.</p>';
		}
		exit;
	}
}
