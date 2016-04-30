#!/usr/bin/php
<?php

require_once("../classes/TF2Backpack.class.php");
$config_file = "../config.json";
$config = json_decode(file_get_contents($config_file), true);

$accounts = $config['admin_accounts'];
foreach($accounts as $username=>$info) {
	echo "Getting backpack for {$username} ({$info['id']})".PHP_EOL;
	$backpack = new TF2Backpack($username);
}

?>