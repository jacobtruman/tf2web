<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("classes/TF2Backpack.class.php");

$username = "jacobtruman";

$backpack = new TF2Backpack($username);

$ret = $backpack->getBackpackHistoryList();
