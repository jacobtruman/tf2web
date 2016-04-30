<head>
	<script type="text/javascript" src="js/jquery-2.1.4.min.js"></script>
	<script type="text/javascript" src="js/jquery.mobile-1.4.5/jquery.mobile-1.4.5.min.js"></script>
	<script type="text/javascript" src="js/jquery.mobile.dynamic.popup.js"></script>

	<link rel="stylesheet" href="js/jquery.mobile-1.4.5/jquery.mobile-1.4.5.min.css" />

	<script>
		$(document).ready(function() {
			$(".tooltip").on({
				click: function () {
					$.dynamic_popup({
						content: this.title,
						'data-position-to': '#'+this.id,
						overlayTheme: "b"
					});
				}
			});
		});
	</script>
</head>
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
$backpack->displayBackpack();

?>

