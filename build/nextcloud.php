<?php
echo "\x1b[33;1m === Nextcloud === \x1b[0m\n";

$nc_destination = "{$destPath}snappymail-{$package->version}-nextcloud.tar";

@unlink($nc_destination);
@unlink("{$nc_destination}.gz");

$nc_tar = new PharData($nc_destination);

$nc_tar->buildFromDirectory('./integrations/nextcloud', "@integrations/nextcloud/snappymail/@");

$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('snappymail/v'), RecursiveIteratorIterator::SELF_FIRST);
foreach ($files as $file) {
	if (is_file($file)) {
		$newFile = str_replace('\\', '/', $file);
//		$newFile = str_replace("'snappymail/v/'.", '', $newFile);
		$nc_tar->addFile($file, "snappymail/app/{$newFile}");
	}
}
/*
$nc_tar->addFile('data/.htaccess');
$nc_tar->addFromString('data/VERSION', $package->version);
$nc_tar->addFile('data/README.md');
$nc_tar->addFile('_include.php', 'snappymail/app/_include.php');
*/
$nc_tar->addFile('.htaccess', 'snappymail/app/.htaccess');

$index = file_get_contents('index.php');
$index = str_replace('0.0.0', $package->version, $index);
//$index = str_replace('snappymail/v/', '', $index);
$nc_tar->addFromString('snappymail/app/index.php', $index);
$nc_tar->addFile('README.md', 'snappymail/app/README.md');
$nc_tar->addFile('CHANGELOG.md', 'snappymail/CHANGELOG.md');

$data = file_get_contents('dev/serviceworker.js');
$nc_tar->addFromString('snappymail/app/serviceworker.js', $data);

$nc_tar->compress(Phar::GZ);
unlink($nc_destination);
$nc_destination .= '.gz';

$signature = shell_exec("openssl dgst -sha512 -sign ~/.nextcloud/certificates/snappymail.key {$nc_destination} | openssl base64");
file_put_contents($nc_destination.'.sig', $signature);

echo "{$nc_destination} created\n";
