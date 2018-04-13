<?php

$phpVersion = phpversion();
if (version_compare($phpVersion, '5.4.0', '<'))
{
	die("PHP 5.4.0 or newer is required. $phpVersion does not meet this requirement. Please ask your host to upgrade PHP.");
}

$dir = __DIR__;

$autoLoader = require($dir . '/src/addons/Artodia/WebDav/vendor/autoload.php');
$autoLoader->register();

require($dir . '/src/XF.php');

XF::start($dir);

$dav = new Artodia\WebDav\App();
$dav->run();
