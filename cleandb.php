<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("classes/TF2Backpack.class.php");

if(isset($_REQUEST['u'])) {
	$username = $_REQUEST['u'];
} else {
	$username = "jacobtruman";
}

$backpack = new TF2Backpack($username);

$backpack->cleanDB();