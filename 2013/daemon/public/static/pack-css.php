<?php
//@author Krzysztof Sikorski
$msg = array();
//minify sources
if(isset($_POST['minify']))
{
	$libPath = explode(DIRECTORY_SEPARATOR, realpath('.'));
	array_pop($libPath);
	array_pop($libPath);
	array_push($libPath, 'lib');
	array_push($libPath, 'jsmin.php');
	$libPath = implode(DIRECTORY_SEPARATOR, $libPath);
	require $libPath;
	$sources = glob('*.src.js');
	foreach($sources as $file)
	{
		list($name) = explode('.', $file, 2);
		$js = file_get_contents($file);
		if(false !== $js)
		{
			$dst = sprintf('%s.js', $name);
			if(false !== file_put_contents($dst, JSMin::minify($js)))
				$msg[] = sprintf('<i>%s</i> minified to <i>%s</i>', $file, $dst);
			else $msg[] = sprintf('<i>%s</i> is not writable', $dst);
		}
		else $msg[] = sprintf('<i>%s</i> is not readable', $file);
	}
}
//list files
$files = glob('*.js');
$baseDir = dirname(__FILE__);
header('Content-Type:text/html; charset=UTF-8', true);
?>
<!DOCTYPE html>
<html lang=en>
<title>JS</title>
<?php
if($msg)
{
	echo'<ul>';
	foreach($msg as $m)
		printf('<li>%s</li>', $m);
	echo'</ul>';
}
?>
<ul>
<?php
foreach($files as $f)
	printf('<li><a href="%1$s">%1$s</a> [%2$d B]</li>', htmlspecialchars($f), filesize($f));
?>
</ul>
<form action="" method=post>
<p><button type=submit name=minify value=1>minify</button></p>
</form>
</html>
