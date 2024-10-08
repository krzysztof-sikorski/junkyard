INTRODUCTION
===

The game was never planned to be released in public,
so it's designed for the LAMP stack it was originally hosted on.
Basically the code assumes some software versions and settings,
and will propably break on different configuration.

REQUIRED SOFTWARE
===

* *Apache 2.x*, with enabled mod_rewrite and .htaccess files.
* *PHP 5.2.x* with settings like in attached php.ini file.
* *MySQL 5.1*, may also work on other 5.x versions.
* *PHPTAL 1.2.1* - newer releases are propably also safe.
* The hosting **MUST** also offer crontab or other similar services.

INSTALLATION
===

1. Prepare database tables using attached SQL file.

2. Configure PHP using attached INI file.

3. Upload game code into server.

4. Download PHPTAL library and upload it into lib subdirectory,
you should now have `lib/PHPTAL.php`, `lib/PHPTAL/Context.php` etc.

5. Enter your hosting's configuration panel and set the `public` subdir
as a domain root, so other files won't be accessible from the Net.

6. Configure crontab: set the `cron.php` file to be executed every day.

7. And now the tricky part: create a config file (or files) and upload
it into `cfg` subdirectory. It's a long and weird topic, details below.

CONFIGURATION
===

The game configuration is handled by the `Daemon_Config` class
(`lib/daemon/config.php` file), which uses it to overwrite its public
properties. Consult the class' constructor for details.

The file's content is simple, it should do nothing more than to return
an associative array of settings. Here's an example of minimal config:

	<?php
	return array(
		'applicationUrl' => 'http://example.com/foo/bar/baz/',
		'applicationMail' => 'daemon@example.com',
		'dbHost' => 'localhost',
		'dbSchema' => 'daemon_db',
		'dbUser' => 'username',
		'dbPassword' => 'some_password',
	);

The tricky part is the config's filename. As you can see in the class'
constructor, it _must_ be exactly the same as the domain on which
the game is hosted, plus the `.php` extension.
For example, if the game is hosted on `example.com`, then the filename
is `example.com.php`. This is designed to prevent accidental overwrites
with config for other machines...

There is also another tricky part: if you execute a script from the
command line instead of URL, then the script uses a special `_cron.php`
file instead of normal config. So you should create that file too.
Or change the `Daemon_Config`'s constructor ;)
