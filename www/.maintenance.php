<?php
	header('HTTP/1.1 503 Service Unavailable');
	header('Retry-After: 300');
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">

  <title>Zmluva o pôžičke - Údržba webstránky</title>

	<style>
		body { color: #333; background: #fff; width: 500px; margin: 100px auto }
		h1 { font: bold 48px/1.5 sans-serif; margin: .6em 0 }
		p { font: 18px/1.5 sans-serif; margin: 1.5em 0 }
	</style>
</head>
<body>
	<h1>Ospravedlňujeme sa,</h1>
	<p>Webstránka je dočasne nedostupná kvôli údržbe</p>
</body>
</html>

<?php
	exit;
