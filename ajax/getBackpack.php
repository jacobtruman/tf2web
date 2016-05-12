<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("../classes/TF2Backpack.class.php");

if(isset($_REQUEST['username'])) {
	$timestamp = NULL;
	#if(!isset($_REQUEST['backpack'])) {
		$backpack = new TF2Backpack($_REQUEST['username']);
	#} else {
	#	$backpack = json_decode($_REQUEST['backpack']);
	#}
	#var_dump($backpack);
	if(isset($_REQUEST['timestamp'])) {
		$timestamp = urldecode($_REQUEST['timestamp']);
	}
	$backpack->displayBackpack($timestamp);
}