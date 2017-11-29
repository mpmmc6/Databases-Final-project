<?php
error_reporting(E_ALL);

function generatePageHTML($title, $body, $stylesheet) {
	$html = <<<EOT
<!DOCTYPE html>
<html>
<head>
<title>$title</title>
<link rel="stylesheet" type="text/css" href="$stylesheet">
</head>
<body>
$body
</body>
</html>
EOT;

	return $html;
}

function redirect($url, $statusCode = 303){
   header('Location: ' . $url, true, $statusCode);
   die();
}

?>