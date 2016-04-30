<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$images_dir = dirname(__FILE__)."/images";

if(!file_exists($images_dir)) {
	mkdir($images_dir, 0777, true);
}

if(!isset($_REQUEST['p'])) {
	exit("The required url parameter \"p\" is not present");
}
$params = json_decode(base64_decode($_REQUEST['p']), true);

if(!isset($params['defindex'])) {
	exit("The required parameter \"defindex\" is not present");
}

$img_file = "{$images_dir}/{$params['defindex']}.png";
if(!file_exists($img_file)) {
	if(!isset($params['image_url'])) {
		exit("The required parameter \"image_url\" is not present");
	}
	$contents = file_get_contents($params['image_url']);
	file_put_contents($img_file, $contents);
} else {
	$contents = file_get_contents($img_file);
}

if(isset($params['dimensions']) && $params['dimensions'] == true && file_exists($img_file)) {
	$size = getimagesize($img_file);
	echo json_encode($size);
} else {
	header('Content-type: image/jpeg');
	echo $contents;
}

?>
