<?php
//@author Krzysztof Sikorski
ini_set('display_errors', '1');
$whitelist = array('127.0.0.1', '::1');
if(isset($_POST['doit']) && in_array(getenv('REMOTE_ADDR'), $whitelist))
{
	header('Content-Type:text/plain;charset=UTF-8');
	require implode(DIRECTORY_SEPARATOR, array(dirname(__FILE__), '..', '..', 'cron.php'));
	exit;
}
header('Content-Type:text/html;charset=UTF-8');
?>
<!doctype HTML>
<html lang=pl>
<meta charset="UTF-8">
<title>Cron</title>
<form action="" method=post>
<p><input type=submit name=doit value=wykonaj></p>
</form>
</html>
