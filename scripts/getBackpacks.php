#!/usr/bin/php
<?php

require_once(dirname(__FILE__)."/../classes/TF2Backpack.class.php");
$config_file = dirname(__FILE__)."/../config.json";
$config = json_decode(file_get_contents($config_file), true);

$accounts = $config['admin_accounts'];
foreach($accounts as $username=>$info) {
	echo "Getting backpack for {$username} ({$info['id']})".PHP_EOL;
	$backpack = new TF2Backpack($username);
}

?>