<?php

// cPanel https://github.com/the-djmaze/snappymail/issues/697
if (defined('APP_PLUGINS_PATH') && !empty($_ENV['CPANEL']) && !is_dir(APP_PLUGINS_PATH.'login-remote')) {
	$asApi = $_ENV['SNAPPYMAIL_INCLUDE_AS_API'];
	$_ENV['SNAPPYMAIL_INCLUDE_AS_API'] = true;

	\SnappyMail\Repository::installPackage('plugin', 'login-remote');

	$aList = \SnappyMail\Repository::getEnabledPackagesNames();
	$aList[] = 'login-remote';
	$oConfig = \RainLoop\Api::Config();
	$oConfig->Set('plugins', 'enable', true);
	$oConfig->Set('plugins', 'enabled_list', \implode(',', \array_unique($aList)));
	$oConfig->Set('login', 'default_domain', 'cpanel');
	$oConfig->Save();

	$sFile = APP_PRIVATE_DATA.'domains/cpanel.json';
	if (!file_exists($sFile)) {
		$config = json_decode(file_get_contents(__DIR__ . '/app/domains/default.json'), true);
		$config['IMAP']['shortLogin'] = true;
		$config['SMTP']['shortLogin'] = true;
		file_put_contents($sFile, json_encode($config, JSON_PRETTY_PRINT));
	}

	$_ENV['SNAPPYMAIL_INCLUDE_AS_API'] = $asApi;
}
