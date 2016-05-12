<head>
	<script type="text/javascript" src="js/jquery-2.1.4.min.js"></script>
	<script type="text/javascript" src="js/jquery.mobile-1.4.5/jquery.mobile-1.4.5.min.js"></script>
	<script type="text/javascript" src="js/jquery.mobile.dynamic.popup.js"></script>

	<link rel="stylesheet" href="js/jquery.mobile-1.4.5/jquery.mobile-1.4.5.min.css"/>

	<script>
		$(document).ready(function () {
			$(".tooltip").on({
				click: function () {
					$.dynamic_popup({
						content: this.title,
						'data-position-to': '#' + this.id,
						overlayTheme: "b"
					});
				}
			});
		});

		function loadBackpack(data) {
			$.ajax({
				method: 'POST',
				url: 'ajax/getBackpack.php',
				data: data,
				success: function (content) {
					$('#content').html(content);
				},
				error: function (xhr, ajaxOptions, thrownError) {
					console.log(xhr);
					console.log(ajaxOptions);
					console.log(thrownError);
				},
				timeout: 15000
			});
		}
	</script>
</head>
<body>
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
$hist = $backpack->getBackpackHistoryList();
$links = array();
$scripts = array();
foreach($hist as $timestamp) {
	$ts = strtotime($timestamp);
	$links[] = "<a id='{$ts}'>{$timestamp} ({$ts})</a><br />";
	$scripts[] = "$('#{$ts}').click(function(event){
	event.preventDefault();
	loadBackpack({username: '{$username}', timestamp: '{$timestamp}'});
});";
}
echo implode(PHP_EOL, $links);

?>
<div id="content">
	<?php
	$backpack->displayBackpack();
	?>
</div>
</body>

<script>
	<?php
	echo implode(PHP_EOL, $scripts);

	//echo "loadBackpack({username: '{$username}'});";
	?>
</script>

